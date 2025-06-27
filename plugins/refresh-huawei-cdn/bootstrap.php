<?php

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

return function (Dispatcher $events) {
    $events->listen(App\Events\PlayerProfileUpdated::class, function ($event) {
        // 获取配置
        $baseUrl = env('HUAWEI_CLOUD_CDN_BASE_URL');
        $iamBaseUrl = env('HUAWEI_CLOUD_IAM_BASE_URL');
        $enterpriseProjectId = env('HUAWEI_CLOUD_ENTERPRISE_PROJECT_ID', '0');
        $username = env('HUAWEI_CLOUD_USERNAME');
        $password = env('HUAWEI_CLOUD_PASSWORD');
        $domainName = env('HUAWEI_CLOUD_DOMAIN_NAME');
        $projectName = env('HUAWEI_CLOUD_PROJECT_NAME');

        // 从缓存中获取Token，如果不存在则获取新的Token
        $authToken = Cache::remember('huawei_cloud_auth_token', 23 * 60, function () use ($iamBaseUrl, $username, $password, $domainName, $projectName) {
            $response = Http::post($iamBaseUrl . '/v3/auth/tokens', [
                'auth' => [
                    'identity' => [
                        'methods' => ['password'],
                        'password' => [
                            'user' => [
                                'domain' => ['name' => $domainName],
                                'name' => $username,
                                'password' => $password,
                            ],
                        ],
                    ],
                    'scope' => [
                        'project' => ['name' => $projectName],
                    ],
                ],
            ]);

            if ($response->successful()) {
                return $response->header('X-Subject-Token');
            } else {
                Log::error('Failed to obtain Huawei Cloud Token: ' . $response->body());
                return null;
            }
        });

        if (!$authToken) {
            return;
        }

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

        $requestBody = [
            'refresh_task' => [
                'type' => 'file',
                'urls' => $urls,
            ],
        ];

        // 请求URL
        $requestUrl = 'https://cdn.myhuaweicloud.com/v1.0/cdn/content/refresh-tasks';

        if ($enterpriseProjectId !== '0') {
            $requestUrl .= '?enterprise_project_id=' . $enterpriseProjectId;
        }

        $response = Http::withHeaders([
            'X-Auth-Token' => $authToken,
            'Content-Type' => 'application/json',
        ])->post($requestUrl, $requestBody);

        if ($response->successful()) {
            $refreshTaskId = $response->json('refresh_task');

        } else {
            // 处理错误
            Log::error('Failed to refresh Huawei Cloud CDN cache: ' . $response->body());
        }
    });
};
