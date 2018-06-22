<?php

namespace Yggdrasil\Controllers;

use DB;
use Exception;
use Yggdrasil\Utils\Log;
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

    public function generate()
    {
        try {
            // 很多 PHP 主机都没有设置 openssl.cnf 这个配置文件，
            // 导致 OpenSSL 扩展的密钥生成功能直接残废，
            // 所以我只好随插件自带一个了。
            $config = [
                'private_key_bits' => 4096,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
                'config' => plugin('yggdrasil-api')->getPath().'/assets/openssl.cnf'
            ];

            $res = openssl_pkey_new($config);

            if (! $res) {
                throw new Exception(openssl_error_string(), 1);
            }

            openssl_pkey_export($res, $privateKey, null, $config);

            return json([
                'errno' => 0,
                'key' => $privateKey
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

        Log::info("[UUID Import] $updated entries updated", [$shouldBeUpdated]);
        Log::info("[UUID Import] $inserted entries inserted", [$shouldBeInserted]);
        Log::info("[UUID Import] $duplicated entries duplicated", [$shouldBeInserted]);

        return json("导入成功，更新了 $updated 条映射，新增了 $inserted 条映射，有 $duplicated 条映射因重复而未导入。", 0);
    }
}
