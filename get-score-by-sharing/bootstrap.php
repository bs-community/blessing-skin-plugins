<?php

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {

    // 加载设置项
    $options = [
        'score_award_per_like' => 50,
        'score_award_per_texture' => 100,
        'take_back_scores_after_deletion' => true
    ];

    foreach ($options as $key => $value) {
        if (! Option::has($key)) {
            Option::set($key, $value);
        }
    }

    // 当用户上传公开材质至皮肤库时奖励积分
    //
    // 在 BS 的材质上传处理逻辑里，是先保存 Texture 再设置积分的，
    // 由于积分是直接在 SkinlibController 缓存的 User 模型里取的，
    // 导致在 texture.created 事件中再怎么设置积分也会被覆盖掉，狗屎。
    // 所以这里只能用一些歪门邪道的方法了（摊手）
    App\Models\Texture::created(function ($t) {
        if ($t->public == 1) {
            $u = app('users')->get($t->uploader);
            // 等到 Application::terminate 的时候再处理
            app()->terminating(function () use ($u) {
                $u->fresh()->setScore(
                    option('score_award_per_texture'), 'plus'
                );
            });
        }
    });

    // 当用户上传的公开材质被删除时，收回之前奖励的积分
    App\Models\Texture::deleted(function ($t) {
        if ($u = app('users')->get($t->uploader)) {
            if ($t->public == 1 && option('take_back_scores_after_deletion')) {
                $u->fresh()->setScore(
                    option('score_award_per_texture'), 'minus'
                );
            }
        }
    });

    // 用户收藏皮肤库中的材质时奖励上传者积分
    App\Models\Texture::updated(function ($t) {
        $uploader = app('users')->get($t->uploader)->fresh();

        // 收藏私密材质或者自己上传的材质不给积分
        if ($t->public != 1 || $t->uploader == auth()->id() || !option('score_award_per_like')) {
            return;
        }

        if (request()->is('user/closet/add')) {
            $uploader->setScore(option('score_award_per_like'), 'plus');
        } else if (request()->is('user/closet/remove')) {
            $uploader->setScore(option('score_award_per_like'), 'minus');
        }
    });

    // 向皮肤库上传页面添加奖励说明
    $events->listen(App\Events\RenderingFooter::class, function ($event) {
        if (! app('request')->is('skinlib/upload')) {
            return;
        }

        $score = option('score_award_per_texture');

        $content = /** @lang JavaScript */
            <<<EOT
blessing.event.on('mounted', () => {
    $('.box-primary > .box-body').append(
        '<div id="msg-2" class="callout callout-success">上传公开材质至皮肤库可以获得 $score 积分奖励。</div>'
    );
})

$('body').on('ifToggled', '[type=checkbox]', function () {
    $(this).prop('checked') ? $('#msg-2').hide() : $('#msg-2').show();
});
EOT;

        $event->addContent("<script>$content</script>");
    });
};
