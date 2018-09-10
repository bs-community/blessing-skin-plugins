<?php

namespace Yggdrasil\Controllers;

use DB;
use Log;
use Exception;
use Datatables;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yggdrasil\Exceptions\NotFoundException;
use Yggdrasil\Exceptions\IllegalArgumentException;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class ConfigController extends Controller
{
    public function hello(Request $request)
    {
        // Default skin domain whitelist:
        // - Specified by option 'site_url'
        // - Extract host from current URL
        $extra = option('ygg_skin_domain') === '' ? [] : explode(',', option('ygg_skin_domain'));
        $skinDomains = array_map('trim', array_unique(array_merge($extra, [
            parse_url(option('site_url'), PHP_URL_HOST),
            $request->getHost()
        ])));

        $privateKey = openssl_pkey_get_private(option('ygg_private_key'));

        if (! $privateKey) {
            throw new IllegalArgumentException('无效的 RSA 私钥，请访问插件配置页重新设置');
        }

        $keyData = openssl_pkey_get_details($privateKey);

        if ($keyData['bits'] < 4096) {
            throw new IllegalArgumentException('RSA 私钥的长度至少为 4096，请访问插件配置页重新设置');
        }

        return json([
            'meta' => [
                'serverName' => option('site_name'),
                'implementationName' => 'Yggdrasil API for Blessing Skin',
                'implementationVersion' => plugin('yggdrasil-api')['version']
            ],
            'skinDomains' => $skinDomains,
            'signaturePublickey' => $keyData['key']
        ]);
    }

    public function logData()
    {
        $query = DB::table('ygg_log')
            ->join('users', 'ygg_log.user_id', '=', 'users.uid')
            ->leftJoin('players', 'ygg_log.player_id', '=', 'players.pid')
            ->select('id', 'action', 'user_id', 'email', 'player_id', 'players.player_name', 'parameters', 'ygg_log.ip', 'time');

        return Datatables::of($query)->make(true);
    }

    public function getRecentActivities()
    {
        // 获取最近的 5 条活动记录
        $entries = DB::table('ygg_log')
            ->leftJoin('players', 'ygg_log.player_id', '=', 'players.pid')
            ->select('action', 'player_name', 'ip', 'time')
            ->where('user_id', auth()->id())
            ->orderBy('time', 'desc')
            ->take(5)
            ->get();

        return $entries;
    }

    public function generate()
    {
        try {
            return json([
                'errno' => 0,
                'key' => ygg_generate_rsa_keys()['private']
            ]);
        } catch (Exception $e) {
            return json('自动生成私钥时出错，请尝试手动设置私钥。错误信息：'.$e->getMessage(), 1);
        }
    }

    public function import(Request $request)
    {
        $json = @json_decode(file_get_contents($request->file('file')));

        if (! $json) {
            return json('不是有效的 JSON 文件。', 1);
        }

        $shouldBeUpdated = [];
        $shouldBeInserted = [];
        $duplicatedEntries = [];

        foreach ($json as $entry) {
            $entry = [
                'name' => $entry->name,
                'uuid' => UUID::format($entry->uuid)
            ];

            $result = DB::table('uuid')->where('name', $entry['name'])->first();

            if ($result) {
                if ($entry['uuid'] == $result->uuid) {
                    $duplicatedEntries[] = $entry;
                } else {
                    $shouldBeUpdated[] = $entry;
                    // Laravel 竟然没有自带批量 Update 数据库的方法，绝了
                    DB::table('uuid')->where('name', $entry['name'])->update(['uuid' => $entry['uuid']]);
                }
            } else {
                $shouldBeInserted[] = $entry;
            }
        }

        // 在一个 SQL 里批量插入
        DB::table('uuid')->insert($shouldBeInserted);

        $updated = count($shouldBeUpdated);
        $inserted = count($shouldBeInserted);
        $duplicated = count($duplicatedEntries);

        Log::channel('ygg')->info("[UUID Import] $updated entries updated", [$shouldBeUpdated]);
        Log::channel('ygg')->info("[UUID Import] $inserted entries inserted", [$shouldBeInserted]);
        Log::channel('ygg')->info("[UUID Import] $duplicated entries duplicated", [$shouldBeInserted]);

        return json("导入成功，更新了 $updated 条映射，新增了 $inserted 条映射，有 $duplicated 条映射因重复而未导入。", 0);
    }
}
