<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        option(['single_player' => true]);
    },
    App\Events\PluginWasDisabled::class => function () {
        option(['single_player' => false]);
    },
];
