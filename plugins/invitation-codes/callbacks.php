<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        if (!Schema::hasTable('invitation_codes')) {
            Schema::create('invitation_codes', function ($table) {
                $table->increments('id');
                $table->string('code', 255);
                $table->dateTime('generated_at');
                $table->integer('used_by')->default(0);
                $table->dateTime('used_at')->nullable();
            });
        }
    },
];
