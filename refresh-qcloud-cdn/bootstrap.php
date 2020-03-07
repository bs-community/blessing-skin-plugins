<?php

use App\Services\Plugin;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(App\Events\PlayerProfileUpdated::class, function ($event) {
        $baseUrl = env('QCLOUD_CDN_BASE_URL');
        $secretKey = env('QCLOUD_CDN_SECRET_KEY');
        $secretId = env('QCLOUD_CDN_SECRET_ID');

        $usm = plugin('usm-api');
        $legacy = plugin('legacy-api');
        $yggdrasil = plugin('yggdrasil-api');

        $name = $event->player->name;
        $urls = [
            $baseUrl . '/' . $name . '.json',
            $baseUrl . '/csl/' . $name . '.json',
        ];

        if (isset($usm) && $usm->enabled) {
            $urls[] = $baseUrl . '/usm/' . $name . '.json';
        }

        if (isset($legacy) && $legacy->enabled) {
            array_push(
                $urls,
                $baseUrl . '/skin/' . $name . '.png',
                $baseUrl . '/cape/' . $name . '.png'
            );
        }

        if (isset($yggdrasil) && $yggdrasil->enabled) {
            $uuid = DB::table('uuid')->where('name', $name)->value('uuid');
            array_push(
                $urls,
                $baseUrl . '/api/yggdrasil/sessionserver/session/minecraft/profile/' . $uuid,
                $baseUrl . '/api/yggdrasil/sessionserver/session/minecraft/profile/' . $uuid . 'unsigned?=false',
                $baseUrl . '/api/yggdrasil/sessionserver/session/minecraft/profile/' . $uuid . 'unsigned=true');
        }

        $apiBody = [
            'Nonce' => rand(),
            'Timestamp' => time(),
            'Action' => 'RefreshCdnUrl',
            'SecretId' => $secretId
        ];

        for ($i = 0; $i < count($urls); $i++) {
            $apiBody['urls.' . $i] = $urls[$i];
        }

        ksort($apiBody);

        $sigTxt = 'POSTcdn.api.qcloud.com/v2/index.php?';
        $isFirst = true;
        foreach ($apiBody as $key => $value) {
            if (! $isFirst) {
                $sigTxt = $sigTxt.'&';
            }
            $isFirst = false;

            // 拼接签名原文时，如果参数名称中携带"_"，需要替换成"."
            if (strpos($key, '_')) {
                $key = str_replace('_', '.', $key);
            }
            $sigTxt = $sigTxt . $key . '=' . $value;
        }

        $signature = base64_encode(hash_hmac('sha1', $sigTxt, $secretKey, true));
        $requestUrl = 'Signature=' . urlencode($signature);
        foreach ($apiBody as $key => $value) {
            $requestUrl = $requestUrl . '&' . $key . '=' . urlencode($value);
        }

        // 开始发出请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestUrl);
        curl_setopt($ch, CURLOPT_URL, 'https://cdn.api.qcloud.com/v2/index.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($ch);  // 忽略响应

    });

};
