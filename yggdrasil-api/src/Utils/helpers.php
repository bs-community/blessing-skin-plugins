<?php

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
    }
}

if (! function_exists('ygg_init_options')) {

    function ygg_init_options()
    {
        $items = [
            'uuid_algorithm' => 'v3',
            'ygg_token_expire_1' => '600',
            'ygg_token_expire_2' => '1200',
            'ygg_rate_limit' => '1000',
            'ygg_skin_domain' => '',
            'ygg_search_profile_max' => '5',
            'ygg_verbose_log' => 'true',
            'ygg_private_key' => ''
        ];
    
        foreach ($items as $key => $value) {
            if (! Option::has($key)) {
                Option::set($key, $value);
            }
        }
    }
}