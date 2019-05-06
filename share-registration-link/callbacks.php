<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        if (! Schema::hasTable('reg_link')) {
            Schema::create('reg_link', function ($table) {
                $table->increments('id');
                $table->integer('sharer');
                $table->string('code', 255);
                $table->timestamps();
            });
        }
    },
];
