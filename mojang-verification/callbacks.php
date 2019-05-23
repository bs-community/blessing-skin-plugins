<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        if (! Schema::hasTable('mojang_verifications')) {
            Schema::create('mojang_verifications', function ($table) {
                $table->increments('id');
                $table->integer('user_id')->unique();
                $table->string('uuid', 32)->unique();
                $table->boolean('verified');
                $table->timestamps();
            });
        }
    },
];
