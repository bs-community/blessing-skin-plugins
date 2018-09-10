<?php

namespace Yggdrasil\Models;

use DB;
use Log;
use App\Models\Player;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Exceptions\IllegalArgumentException;

class Profile
{
    public $uuid;
    public $name;
    public $player;
    public $model = "default";
    public $skin;
    public $cape;

    public function sign($data, $key)
    {
        openssl_sign($data, $sign, $key);

        return $sign;
    }

    public function serialize($unsigned = null)
    {
        // 如果没显示指定 `unsigned` 参数就从 URL 中推断
        if (is_null($unsigned)) {
            $unsigned = is_null(request('unsigned')) || request('unsigned') === 'true';
        }

        $textures = [
            'timestamp' => round(microtime(true) * 1000),
            'profileId' => UUID::format($this->uuid),
            'profileName' => $this->name,
            'isPublic' => true,
            'textures' => []
        ];

        // 检查 RSA 私钥
        if ($unsigned === false) {
            $key = openssl_pkey_get_private(option('ygg_private_key'));

            if (! $key) {
                throw new IllegalArgumentException('无效的 RSA 私钥，请访问插件配置页设置');
            }

            $textures['signatureRequired'] = true;
        }

        // 避免 BungeeCord 服务器上可能出现无法加载材质的 Bug
        app('url')->forceRootUrl(option('site_url'));

        if ($this->skin != "") {
            $textures['textures']['SKIN'] = [
                'url' => url("textures/{$this->skin}")
            ];

            if ($this->model == "slim") {
                $textures['textures']['SKIN']['metadata'] = ['model' => 'slim'];
            }
        }

        if ($this->cape != "") {
            $textures['textures']['CAPE'] = [
                'url' => url("textures/{$this->cape}")
            ];
        }

        $result = [
            'id' => UUID::format($this->uuid),
            'name' => $this->name,
            'properties' => [
                [
                    'name' => 'textures',
                    'value' => base64_encode(
                        json_encode($textures, JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT)
                    )
                ]
            ]
        ];

        if ($unsigned === false) {
            // 给每个 properties 签名
            foreach ($result['properties'] as &$prop) {
                $signature = $this->sign($prop['value'], $key);

                $prop['signature'] = base64_encode($signature);
            }

            unset($prop);
            openssl_free_key($key);
        }

        return json_encode($result, JSON_UNESCAPED_SLASHES);
    }

    public function __toString()
    {
        return $this->serialize();
    }

    public static function getUuidFromName($name)
    {
        $result = DB::table('uuid')->where('name', $name)->first();

        if (! $result) {
            // 分配新的 UUID
            $result = UUID::generateMinecraftUuid($name)->clearDashes();
            DB::table('uuid')->insert(['name' => $name, 'uuid' => $result]);

            Log::channel('ygg')->info("New uuid [$result] allocated to player [$name]");
        } else {
            $result = $result->uuid;
        }

        return $result;
    }

    public static function createFromUuid($uuid)
    {
        $result = DB::table('uuid')->where('uuid', $uuid)->first();

        if ($result && ($player = Player::where('player_name', $result->name)->first())) {
            return static::createFromPlayer($player);
        }
    }

    public static function createFromPlayer(Player $player)
    {
        $profile = new static();

        $profile->uuid = static::getUuidFromName($player->player_name);
        $profile->name = $player->player_name;
        $profile->model = $player->getPreference();
        $profile->player = $player;
        $profile->skin = $player->getTexture('skin');
        $profile->cape = $player->getTexture('cape');

        return $profile;
    }
}
