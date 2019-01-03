<?php

use Log;

if (! function_exists('ygg_log_path')) {

    function ygg_log_path()
    {
        $dbConfig = config('database.connections.'.config('database.default'));
        $mask = substr(md5(implode(',', array_values($dbConfig))), 0, 8);

        return storage_path("logs/yggdrasil-$mask.log");
    }
}

if (! function_exists('ygg_generate_rsa_keys')) {

    function ygg_generate_rsa_keys($config = [])
    {
        /**
         * 很多 PHP 主机都没有设置 openssl.cnf 这个配置文件，
         * 导致 OpenSSL 扩展的数字签名和密钥生成功能直接残废，
         * 所以我只好随插件自带一个了。
         */
        $config = array_merge($config, [
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'config' => plugin('yggdrasil-api')->getPath().'/assets/openssl.cnf'
        ]);

        $res = openssl_pkey_new($config);

        if (! $res) {
            throw new Exception(openssl_error_string(), 1);
        }

        openssl_pkey_export($res, $privateKey, null, $config);

        return [
            'private' => $privateKey,
            'public'  => openssl_pkey_get_details($res)['key']
        ];
    }
}

if (! function_exists('ygg_init_db_tables')) {

    function ygg_init_db_tables()
    {
        if (! Schema::hasTable('uuid')) {
            Schema::create('uuid', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('uuid', 255);
            });
        }

        if (! Schema::hasTable('ygg_log')) {
            Schema::create('ygg_log', function ($table) {
                $table->increments('id');
                $table->string('action');
                $table->integer('user_id');
                $table->integer('player_id');
                $table->string('parameters')->default('');
                $table->string('ip')->default('');
                $table->dateTime('time');
            });
        }
    }
}

if (! function_exists('ygg_init_options')) {

    function ygg_init_options()
    {
        $items = [
            'ygg_uuid_algorithm' => 'v3',
            'ygg_token_expire_1' => '259200', // 3 days
            'ygg_token_expire_2' => '604800', // 7 days
            'ygg_rate_limit' => '1000',
            'ygg_skin_domain' => '',
            'ygg_search_profile_max' => '5',
            'ygg_private_key' => '',
            'ygg_show_config_section' => 'true',
            'ygg_show_activities_section' => 'true',
            'ygg_enable_ali' => 'true'
        ];

        foreach ($items as $key => $value) {
            if (! Option::has($key)) {
                Option::set($key, $value);
            }
        }

        $originalDefaultValue = [
            'ygg_token_expire_1' => '600',
            'ygg_token_expire_2' => '1200'
        ];

        // 原来的令牌过期时间默认值太低了，调高点
        foreach ($originalDefaultValue as $key => $value) {
            if (Option::get($key) == $value) {
                Option::set($key, $items[$key]);
            }
        }

        if (! menv('YGG_VERBOSE_LOG')) {
            // 删就完事儿了
            @unlink(ygg_log_path());
            @unlink(storage_path('logs/yggdrasil.log'));
        }
    }
}

if (! function_exists('ygg_log_http_request_and_response')) {

    function ygg_log_http_request_and_response()
    {
        Log::channel('ygg')->info('============================================================');
        Log::channel('ygg')->info(request()->method(), [request()->path()]);

        Event::listen('kernel.handled', function ($request, $response) {
            $statusCode = $response->getStatusCode();
            $statusText = Symfony\Component\HttpFoundation\Response::$statusTexts[$statusCode];
            Log::channel('ygg')->info(sprintf('HTTP/%s %s %s', $response->getProtocolVersion(), $statusCode, $statusText));
        });
    }
}

if (! function_exists('ygg_log')) {

    function ygg_log($params)
    {
        $data = array_merge([
            'action' => 'undefined',
            'user_id' => 0,
            'player_id' => 0,
            'parameters' => '[]',
            'ip' => get_client_ip(),
            'time' => get_datetime_string()
        ], $params);

        return DB::table('ygg_log')->insert($data);
    }
}
