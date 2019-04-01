<?php

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(App\Events\PlayerProfileUpdated::class, function ($event) {
        $name = $event->player->name;
        $url[0] = env('QCLOUD_CDN_BASE_URL') . "/$name.json";
        $url[1] = env('QCLOUD_CDN_BASE_URL') . "/csl/$name.json";
        $url[2] = env('QCLOUD_CDN_BASE_URL') . "/usm/$name.json";

        $secretKey = env('QCLOUD_CDN_SECRET_KEY');
        $secretId = env('QCLOUD_CDN_SECRET_ID');

        $apiBody = [
            'Nonce' => rand(),
            'Timestamp' => time(NULL),
            'Action' => 'RefreshCdnUrl',
            'SecretId' => $secretId,
            'urls.0' => $url[0],
            'urls.1' => $url[1],
            'urls.2' => $url[2],
        ];
        ksort($apiBody);

        $sigTxt = 'POSTcdn.api.qcloud.com/v2/index.php?';
        $isFirst = true;
        foreach ($apiBody as $key => $value) {
            if (! $isFirst) {
                $sigTxt = $sigTxt.'&';
            }
            $isFirst = false;

            // 拼接签名原文时，如果参数名称中携带"_"，需要替换成"."
            if(strpos($key, '_')) {
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
