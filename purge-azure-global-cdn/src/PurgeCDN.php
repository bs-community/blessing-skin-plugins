<?php

namespace Honoka\PurgeAzureGlobalCdn;

use App\Models\Player;
use App\Services\PluginManager;
use Composer\CaBundle\CaBundle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PurgeCDN implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /** @var Player */
    protected $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function handle(PluginManager $plugins)
    {
        // 检查插件是否启用
        $usm = $plugins->get('usm-api');
        $legacy = $plugins->get('legacy-api');
        $yggdrasil = $plugins->get('yggdrasil-api');

        // 列出需要刷新的 URL
        $name = urlencode($this->player->name);
        $urls = ['/'. $name.'.json', '/csl/'.$name.'.json'];
        if (isset($usm) && $usm->isEnabled()) {
            $urls[] = '/usm/' . $name . '.json';
        }
        if (isset($legacy) && $legacy->isEnabled()) {
            array_push(
                $urls,
                '/skin/' . $name . '.png',
                '/cape/' . $name . '.png'
            );
        }
        if (isset($yggdrasil) && $yggdrasil->isEnabled()) {
            $uuid = DB::table('uuid')->where('name', $name)->value('uuid');
            array_push(
                $urls,
                '/api/yggdrasil/sessionserver/session/minecraft/profile/' . $uuid,
                '/api/yggdrasil/sessionserver/session/minecraft/profile/' . $uuid . '?unsigned=false',
                '/api/yggdrasil/sessionserver/session/minecraft/profile/' . $uuid . '?unsigned=true'
            );
        }

        // 获取 Access Token
        $token = Cache::get('AZURE_ACCESS_TOKEN', function () {
            $response = Http::withOptions([
                'query' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => env('AZURE_AD_CLIENT_ID'),
                    'client_secret' => env('AZURE_AD_CLIENT_SECRET'),
                    'resource' => 'https://management.azure.com/',
                ],
                'verify' => CaBundle::getSystemCaRootBundlePath(),
            ])->post('https://login.microsoftonline.com/'.env('AZURE_AD_TENANT_ID').'/oauth2/token');

            $result = $response->json();
            Cache::put('AZURE_ACCESS_TOKEN', $result['access_token'], (int) $result['expires_in']);

            return $result['access_token'];
        });

        // 请求清除缓存
        Http::withHeaders([
            'Authorization' => "Bearer $token",
            'Content-Type' => 'application/json',
        ])->withOptions([
            'body' => json_encode(['contentPaths' => $urls], JSON_UNESCAPED_SLASHES),
            'verify' => CaBundle::getSystemCaRootBundlePath(),
        ])->post('https://management.azure.com/subscriptions/'.env('AZURE_SUBSCRIPTION_ID').'/resourceGroups/'.env('AZURE_RESOURCE_GROUP').'/providers/Microsoft.Cdn/profiles/'.env('AZURE_CDN_PROFILE').'/endpoints/'.env('AZURE_CDN_ENDPOINT').'/purge?api-version=2019-04-15');
    }
}
