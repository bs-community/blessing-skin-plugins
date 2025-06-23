<?php

require __DIR__.'/src/Utils/helpers.php';

use App\Models\Scope;
use App\Services\Facades\Option;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return [
    App\Events\PluginWasEnabled::class => function () {
        if (!Scope::where('name', 'openid')->exists()) {
            Scope::create([
                'name' => 'openid',
                'description' => 'LittleSkin\\YggdrasilConnect::scopes.openid',
            ]);
        }

        if (!Scope::where('name', 'profile')->exists()) {
            Scope::create([
                'name' => 'profile',
                'description' => 'LittleSkin\\YggdrasilConnect::scopes.profile',
            ]);
        }

        if (!Scope::where('name', 'offline_access')->exists()) {
            Scope::create([
                'name' => 'offline_access',
                'description' => 'LittleSkin\\YggdrasilConnect::scopes.offline-access',
            ]);
        }

        if (!Scope::where('name', 'Yggdrasil.PlayerProfiles.Read')->exists()) {
            Scope::create([
                'name' => 'Yggdrasil.PlayerProfiles.Read',
                'description' => 'LittleSkin\\YggdrasilConnect::scopes.player-profiles.read',
            ]);
        }

        if (!Scope::where('name', 'Yggdrasil.PlayerProfiles.Select')->exists()) {
            Scope::create([
                'name' => 'Yggdrasil.PlayerProfiles.Select',
                'description' => 'LittleSkin\\YggdrasilConnect::scopes.player-profiles.select',
            ]);
        }

        if (!Scope::where('name', 'Yggdrasil.Server.Join')->exists()) {
            Scope::create([
                'name' => 'Yggdrasil.Server.Join',
                'description' => 'LittleSkin\\YggdrasilConnect::scopes.server.join',
            ]);
        }

        if (!Schema::hasTable('uuid')) {
            Schema::create('uuid', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pid')->unique();
                $table->foreign('pid')->references('pid')->on('players')->cascadeOnDelete();
                $table->string('name')->unique();
                $table->string('uuid', 255)->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ygg_log')) {
            Schema::create('ygg_log', function (Blueprint $table) {
                $table->increments('id');
                $table->string('action');
                $table->integer('user_id');
                $table->integer('player_id');
                $table->string('parameters', 2048)->default('');
                $table->string('ip')->default('');
                $table->dateTime('time');
            });
        }

        if (!Schema::hasTable('code_id_to_uuid')) {
            Schema::create('code_id_to_uuid', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code_id')->unique();
                $table->foreign('code_id')->references('id')->on('oauth_auth_codes')->cascadeOnDelete();
                $table->string('uuid');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        $items = [
            'ygg_uuid_algorithm' => 'v3',
            'ygg_token_expire_1' => '259200', // 3 days
            'ygg_token_expire_2' => '604800', // 7 days
            'ygg_tokens_limit' => '10',
            'ygg_rate_limit' => '1000',
            'ygg_skin_domain' => '',
            'ygg_search_profile_max' => '5',
            'ygg_private_key' => '',
            'ygg_show_config_section' => 'true',
            'ygg_show_activities_section' => 'true',
            'ygg_enable_ali' => 'true',
            'ygg_disable_authserver' => 'false',
            'ygg_connect_server_url' => '',
        ];

        foreach ($items as $key => $value) {
            if (!Option::get($key)) {
                Option::set($key, $value);
            }
        }

        $originalDefaultValue = [
            'ygg_token_expire_1' => '600',
            'ygg_token_expire_2' => '1200',
        ];

        // 原来的令牌过期时间默认值太低了，调高点
        foreach ($originalDefaultValue as $key => $value) {
            if (Option::get($key) == $value) {
                Option::set($key, $items[$key]);
            }
        }

        if (!env('YGG_VERBOSE_LOG')) {
            @unlink(storage_path('logs/yggdrasil.log'));
        }

        // 从旧版升级上来的默认继续使用旧的 UUID 生成算法
        if (DB::table('uuid')->count() > 0 && !Option::get('ygg_uuid_algorithm')) {
            Option::set('ygg_uuid_algorithm', 'v4');
        }

        // 初次使用自动生成私钥
        if (option('ygg_private_key') == '') {
            option(['ygg_private_key' => ygg_generate_rsa_keys()['private']]);
        }

        if (!config('jwt.secret')) {
            $key = Str::random(64);
            config(['jwt.secret' => $key]);

            $path = app()->environmentFilePath();
            $content = file_get_contents($path);
            file_put_contents($path, $content.PHP_EOL.'JWT_SECRET='.$key);
        }
    },
];
