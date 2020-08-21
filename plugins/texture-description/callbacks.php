<?php

use Blessing\TextureDescription\Models\Description;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return [
    App\Events\PluginWasEnabled::class => function () {
        if (!Schema::hasTable('textures_description')) {
            Schema::create('textures_description', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('tid')->unsigned()->unique();
                $table->string('description');
            });
        }

        if (Schema::hasTable('textures_desc')) {
            DB::table('textures_desc')
                ->get()
                ->each(function ($row) {
                    Description::create([
                        'tid' => $row->tid,
                        'description' => $row->desc,
                    ]);
                });
            Schema::dropIfExists('textures_desc');
        }

        $oldOption = option('textures_desc_limit');
        if ($oldOption) {
            option(['textures_description_limit' => $oldOption]);
            DB::table('options')->where('option_name', 'textures_desc_limit')->delete();
        }
    },
];
