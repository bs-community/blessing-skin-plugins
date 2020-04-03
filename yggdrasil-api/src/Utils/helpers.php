<?php

use Carbon\Carbon;
use Vectorface\Whip\Whip;

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
        if (env('YGG_VERBOSE_LOG')) {
            $data = array_merge([
                'action' => 'undefined',
                'user_id' => 0,
                'player_id' => 0,
                'parameters' => '[]',
                'ip' => (new Whip())->getValidIpAddress(),
                'time' => Carbon::now(),
            ], $params);

            return DB::table('ygg_log')->insert($data);
        }
    }
}
