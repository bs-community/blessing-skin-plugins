<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        if (config('secure.cipher') == 'SALTED2MD5' && !Schema::hasColumn('users', 'salt')) {
            Schema::table('users', function ($table) {
                $table->string('salt', 6)->default('');
            });
        }

        if (!Schema::hasColumn('users', 'forum_uid')) {
            Schema::table('users', function ($table) {
                $table->integer('forum_uid', false, false)->nullable();
            });
        }
    },
];

