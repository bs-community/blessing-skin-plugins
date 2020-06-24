<?php
/**
 * Originally created by Matt Mullenweg as a WordPress plugin,
 * migrated to Blessing Skin Server by printempw.
 */
use App\Services\Hook;
use App\Services\Plugin;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events, Plugin $plugin) {
    Hook::addScriptFileToPage(
        $plugin->assets('hello-dolly.js'),
        ['user', 'user/*', 'admin', 'admin/*']
    );

    $events->listen(App\Events\RenderingHeader::class, function ($event) {
        // We need some CSS to position the paragraph
        $event->addContent('<style> .dolly { display: inline; margin-left: 15px; } </style>');
    });
};
