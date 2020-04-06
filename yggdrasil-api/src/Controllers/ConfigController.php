<?php

namespace Yggdrasil\Controllers;

use DB;
use Log;
use Option;
use Exception;
use App\Services\Hook;
use Yggdrasil\Utils\UUID;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yggdrasil\Exceptions\NotFoundException;
use Yggdrasil\Exceptions\IllegalArgumentException;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class ConfigController extends Controller
{
    public function render()
    {
        $commonForm = Option::form('common', '常规配置', function($form) {
            $form->select('ygg_uuid_algorithm', 'UUID 生成算法')
                ->option('v3', 'Version 3: 与原盗版用户 UUID 一致【推荐】')
                ->option('v4', 'Version 4: 随机生成【想要同时兼容盗版登录的不要选】')
                ->hint('选择 Version 3 以获得对原盗版服务器的最佳兼容性。');
            $form->text('ygg_token_expire_1', '令牌暂时失效时间');
            $form->text('ygg_token_expire_2', '令牌完全失效时间')
                ->description('分别指定 Token【暂时失效】与【完全失效】的过期时间（技术细节请参阅 http://t.cn/RHKshKe），单位为秒');
            $form->text('ygg_rate_limit', '登录/登出频率限制')
                ->hint('两次操作之间的时间间隔（毫秒）');
            $form->text('ygg_skin_domain', '额外皮肤白名单域名')
                ->description('只有在此列表中的材质才能被加载。【本站地址】和【当前访问地址】已经默认添加至白名单列表，需要添加的额外白名单域名请使用半角逗号 (,) 分隔');
            $form->text('ygg_search_profile_max', '批量查询角色数量限制')
                ->hint('一次请求中最多能查询几个角色');
            $form->checkbox('ygg_show_config_section', '显示快速配置板块')
                ->label('在用户中心首页显示「快速配置启动器」板块');
            $form->checkbox('ygg_enable_ali', 'API 地址指示')
                ->label('开启「API 地址指示 (ALI)」功能');
        })->handle();

        $keypairForm = Option::form('keypair', '密钥对配置', function($form) {
            $form->textarea('ygg_private_key', 'OpenSSL 私钥')
                ->rows(10)
                ->hint('只需填写 PEM 格式的私钥即可，公钥会根据私钥自动生成。');
            })->renderWithOutSubmitButton()->addButton([
                'style' => 'success',
                'name' => 'generate-key',
                'text' => '帮我生成一个私钥',
            ])->addButton([
                'style' => 'primary',
                'type' => 'submit',
                'name' => 'submit-key',
                'class' => 'pull-right',
                'text' => '保存私钥',
            ])->addMessage('使用下方的按钮来自动生成符合格式的私钥。<br>如需自定义用于签名的私钥，请参阅 <a href="https://github.com/yushijinhun/authlib-injector/wiki/%E7%AD%BE%E5%90%8D%E5%AF%86%E9%92%A5%E5%AF%B9">Wiki - 签名密钥对</a>。')->handle();

        if (! openssl_pkey_get_private(option('ygg_private_key'))) {
            $keypairForm->addMessage('无效的私钥，请检查后重新配置。', 'danger');
        } else {
            $keypairForm->addMessage('私钥有效。', 'success');
        }

        Hook::addScriptFileToPage(plugin_assets('yggdrasil-api', 'config.js'));

        return view('Yggdrasil::config', [
            'forms' => ['common' => $commonForm, 'keypair' => $keypairForm],
        ]);
    }

    public function hello(Request $request)
    {
        // Default skin domain whitelist:
        // - Specified by option 'site_url'
        // - Extract host from current URL
        $extra = option('ygg_skin_domain') === '' ? [] : explode(',', option('ygg_skin_domain'));
        $skinDomains = array_map('trim', array_values(array_unique(array_merge($extra, [
            parse_url(option('site_url'), PHP_URL_HOST),
            $request->getHost()
        ]))));

        $privateKey = openssl_pkey_get_private(option('ygg_private_key'));

        if (! $privateKey) {
            throw new IllegalArgumentException('无效的 RSA 私钥，请访问插件配置页重新设置');
        }

        $keyData = openssl_pkey_get_details($privateKey);

        if ($keyData['bits'] < 4096) {
            throw new IllegalArgumentException('RSA 私钥的长度至少为 4096，请访问插件配置页重新设置');
        }

        $result = [
            'meta' => [
                'serverName' => option_localized('site_name'),
                'implementationName' => 'Yggdrasil API for Blessing Skin',
                'implementationVersion' => plugin('yggdrasil-api')->version,
                'links' => [
                    'homepage' => url('/')
                ]
            ],
            'skinDomains' => $skinDomains,
            'signaturePublickey' => $keyData['key'],
        ];

        if (option('user_can_register')) {
            $result['meta']['links']['register'] = url('auth/register');
        }

        return json($result);
    }

    public function logPage()
    {
        $logs = DB::table('ygg_log')->paginate(10);
        $actions = [
            'authenticate' => '登录',
            'refresh' => '刷新令牌',
            'validate' => '验证令牌',
            'signout' => '登出',
            'invalidate' => '吊销令牌',
            'join' => '请求加入服务器',
            'has_joined' => '进入服务器',
            'undefined' => '未知',
        ];

        return view('Yggdrasil::log', ['logs' => $logs, 'actions' => $actions]);
    }

    public function generate()
    {
        try {
            return json([
                'code' => 0,
                'key' => ygg_generate_rsa_keys()['private'],
            ]);
        } catch (Exception $e) {
            return json('自动生成私钥时出错，请尝试手动设置私钥。错误信息：'.$e->getMessage(), 1);
        }
    }
}
