<?php

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {

    $events->listen(App\Events\RenderingFooter::class, function($event) {
        $content = <<< EOT
<script>
if ($('.breadcrumb').length > 0) {
    $('.breadcrumb').append("<p class='hitokoto'></p>");
} else {
    $('.content-header').append("<div class='breadcrumb'><p class='hitokoto'></p></div>");
}

fetch("https://v1.hitokoto.cn?encode=json").then(function(response) {
    return response.json();
}).then(function(data) {
    $('.hitokoto').text(data.hitokoto);
}).
catch(function(err) {
});
</script>
EOT;

        $event->addContent($content);
    });

    $events->listen(App\Events\RenderingHeader::class, function($event) {
        // We need some CSS to position the paragraph
        $event->addContent('<style> .hitokoto { display: inline; margin-left: 15px; } </style>');
    });

};
