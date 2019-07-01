<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    Hook::addScriptFileToPage(plugin_assets('hitokoto', 'hitokoto.js'));

    $events->listen(App\Events\RenderingHeader::class, function($event) {
        // We need some CSS to position the paragraph
        $event->addContent('<style> .hitokoto { display: inline; margin-left: 15px; } </style>');
    });

};
