<?php

use App\Services\Plugin;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(App\Events\PlayerProfileUpdated::class, function ($event) {
        // 检查插件是否启用
        $usm = plugin('usm-api');
        $legacy = plugin('legacy-api');
        $yggdrasil = plugin('yggdrasil-api');

        // 列出需要刷新的 URL
        $name = $event->player->name;
        $urls = [
            '/' . $name . '.json',
            '/csl/' . $name . '.json'
        ];

        if (isset($usm) && $usm->enabled) {
            $urls[] = '/usm/' . $name . '.json';
        }

        if (isset($legacy) && $legacy->enabled) {
            array_push(
                $urls,
                '/skin/' . $name . '.png',
                '/cape/' . $name . '.png'
            );
        }

        if (isset($yggdrasil) && $yggdrasil->enabled) {
            $uuid = DB::table('uuid')->where('name', $name)->value('uuid');
            array_push(
                $urls,
                '/api/yggdrasil/sessionserver/session/minecraft/profile/' . $uuid,
                '/api/yggdrasil/sessionserver/session/minecraft/profile/' . $uuid . 'unsigned?=false',
                '/api/yggdrasil/sessionserver/session/minecraft/profile/' . $uuid . 'unsigned=true');
        }

        // 获取 Access Token
        Cache::forget('AZURE_ACCESS_TOKEN');
        $token = Cache::get('AZURE_ACCESS_TOKEN');
        if(!isset($token)) {
            $postfield =http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => env('AZURE_AD_CLIENT_ID'),
                'client_secret' => env('AZURE_AD_CLIENT_SECRET'),
                'resource' => 'https://management.azure.com/'
            ]);
            $posturl = 'https://login.microsoftonline.com/' . env('AZURE_AD_TENANT_ID') . '/oauth2/token';
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postfield,
                CURLOPT_URL => $posturl,
                CURLOPT_RETURNTRANSFER => true
            ]);
            $response = [
                'body' => json_decode(curl_exec($ch), true),
                'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE)
            ];
            Cache::put('AZURE_ACCESS_TOKEN', $response['body']['access_token'], $response['body']['expires_in']);
            $token = $response['body']['access_token'];
        }

        // 请求清除缓存
        logger($token);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode(['contentPaths' => $urls], JSON_UNESCAPED_SLASHES),
            CURLOPT_URL => 'https://management.azure.com/subscriptions/' . env('AZURE_SUBSCRIPTION_ID') . '/resourceGroups/' . env('AZURE_RESOURCE_GROUP') . '/providers/Microsoft.Cdn/profiles/' . env('AZURE_CDN_PROFILE') . '/endpoints/' . env('AZURE_CDN_ENDPOINT') .  '/purge?api-version=2019-04-15',
        ]);
        curl_exec($ch);
    });

};
