<?php

use Illuminate\Database\Schema\Blueprint;

return [
    App\Events\PluginWasEnabled::class => function () {
        if (!Schema::hasTable('textures_desc')) {
            Schema::create('textures_desc', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('tid')->unsigned()->unique();
                $table->string('desc');
            });
        }
    },
];
