<?php

use App\Services\Plugin;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(App\Events\PlayerProfileUpdated::class, function ($event) {
        // 获取配置
        $baseUrl = env('ALICDN_SITE_BASE_URL');
        $AccessKeyId = env('ALICDN_ACCESSKEY_ID');
        $AccessKeySecret = env('ALICDN_ACCESSKEY_SECRET');

        // 检测插件
        $usm = plugin('usm-api');
        $legacy = plugin('legacy-api');
        $yggdrasil = plugin('yggdrasil-api');

        $name = $event->player->name;
        $urls = [
            $baseUrl.'/'.$name.'.json',
            $baseUrl.'/csl/'.$name.'.json',
        ];

        if (isset($usm) && $usm->isEnabled()) {
            $urls[] = $baseUrl.'/usm/'.$name.'.json';
        }

        if (isset($legacy) && $legacy->isEnabled()) {
            array_push(
                $urls,
                $baseUrl.'/skin/'.$name.'.png',
                $baseUrl.'/cape/'.$name.'.png'
            );
        }

        if (isset($yggdrasil) && $yggdrasil->isEnabled()) {
            $uuid = DB::table('uuid')->where('name', $name)->value('uuid');
            array_push(
                $urls,
                $baseUrl.'/api/yggdrasil/sessionserver/session/minecraft/profile/'.$uuid,
                $baseUrl.'/api/yggdrasil/sessionserver/session/minecraft/profile/'.$uuid.'?unsigned=false',
                $baseUrl.'/api/yggdrasil/sessionserver/session/minecraft/profile/'.$uuid.'?unsigned=true'
            );
        }

        $need_refresh_url = '';

        // 构建需要刷新URL链接
        foreach ($urls as $k => $v) {
            if ($k === (sizeof($urls) - 1)) {
                $need_refresh_url = $need_refresh_url.$v;
            } else {
                $need_refresh_url = $need_refresh_url.$v.'\n';
            }
        }

        // API请求Query数组
        $API_Query = [
            'Action' => 'RefreshObjectCaches',
            'ObjectPath' => $need_refresh_url,
            'ObjectType' => 'File',
            'Format' => 'JSON',
            'Version' => '2018-05-10',
            'AccessKeyId' => $AccessKeyId,
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => bin2hex(random_bytes(16)),
            'SignatureVersion' => '1.0',
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
        ];

        // 构造规范化请求字符串
        ksort($API_Query);
        $canonicalizedQueryString = '';
        foreach ($API_Query as $key => $value) {
            $k = urlencode($key);
            $v = urlencode($value);
            // 加号（+）替换为 %20、星号（*）替换为 %2A、%7E 替换为波浪号（~）
            $k = preg_replace('/\+/', '%20', $k);
            $k = preg_replace('/\*/', '%2A', $k);
            $k = preg_replace('/%7E/', '~', $k);
            $v = preg_replace('/\+/', '%20', $v);
            $v = preg_replace('/\*/', '%2A', $v);
            $v = preg_replace('/%7E/', '~', $v);
            $canonicalizedQueryString .= ('&'.$k.'='.$v);
        }

        // 构造签名字符串
        $SignText = urlencode(substr($canonicalizedQueryString, 1));
        // 加号（+）替换为 %20、星号（*）替换为 %2A、%7E 替换为波浪号（~）
        $SignText = preg_replace('/\+/', '%20', $SignText);
        $SignText = preg_replace('/\*/', '%2A', $SignText);
        $SignText = preg_replace('/%7E/', '~', $SignText);
        $StringToSign = 'GET&%2F&'.$SignText;
        $Signature = base64_encode(hash_hmac('sha1', $StringToSign, ($AccessKeySecret.'&'), true));

        // URL拼接
        $API_Query['Signature'] = $Signature;
        $requestUrl = 'https://cdn.aliyuncs.com/?'.http_build_query($API_Query);

        // 发出请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($ch);
    });
};
