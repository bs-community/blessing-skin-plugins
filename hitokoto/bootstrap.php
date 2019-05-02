<?php

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {

    $events->listen(App\Events\RenderingFooter::class, function($event) {
        // Get Hitokoto
	    try {
            $hitokoto = file_get_contents("https://v1.hitokoto.cn/?encode=text");
        } catch(Exception $e) {
            $hitokoto = "";
        }
        $content = <<< EOT
<script>
if ($('.breadcrumb').length > 0) {
    $('.breadcrumb').append("<p class='hitokoto'>$hitokoto</p>");
} else {
    $('.content-header').append("<div class='breadcrumb'><p class='hitokoto'>$hitokoto</p></div>");
}
</script>
EOT;

        $event->addContent($content);
    });

    $events->listen(App\Events\RenderingHeader::class, function($event) {
        // We need some CSS to position the paragraph
        $event->addContent('<style> .hitokoto { display: inline; margin-left: 15px; } </style>');
    });

};

