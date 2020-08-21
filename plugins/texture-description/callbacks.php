<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return [
    App\Events\PluginWasEnabled::class => function () {
        if (Schema::hasTable('textures_desc')) {
            Schema::table('textures_desc', function (Blueprint $table) {
                $table->renameColumn('desc', 'description');
                $table->rename('textures_description');
            });
        } elseif (!Schema::hasTable('textures_description')) {
            Schema::create('textures_description', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('tid')->unsigned()->unique();
                $table->string('description');
            });
        }

        DB::table('options')
            ->where('option_name', 'textures_desc_limit')
            ->update(['option_name' => 'textures_description_limit']);
    },
];
