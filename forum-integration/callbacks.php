<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        option(['single_player' => true]);

        if (config('secure.cipher') == 'SALTED2MD5' && ! Schema::hasColumn('users', 'salt')) {
            Schema::table('users', function ($table) {
                $table->string('salt', 6)->default('');
            });
        }
    },
    App\Events\PluginWasDisabled::class => function () {
        option(['single_player' => false]);
    },
];
