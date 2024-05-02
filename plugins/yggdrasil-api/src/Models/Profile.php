<?php

namespace Yggdrasil\Models;

use App\Models\Player;
use App\Models\Texture;
use Cache;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Log;
use Ramsey\Uuid\Uuid;
use Schema;
use Yggdrasil\Exceptions\IllegalArgumentException;

class Profile
{
    public $uuid;
    public $name;
    public $player;
    public $model = 'default';
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
            'profileId' => str_replace('-', '', $this->uuid),
            'profileName' => $this->name,
            'isPublic' => true,
            'textures' => [],
        ];

        // 检查 RSA 私钥
        if ($unsigned === false) {
            $key = openssl_pkey_get_private(option('ygg_private_key'));

            if (!$key) {
                throw new IllegalArgumentException(trans('Yggdrasil::config.rsa.invalid'));
            }

            $textures['signatureRequired'] = true;
        }

        // 避免 BungeeCord 服务器上可能出现无法加载材质的 Bug
        app('url')->forceRootUrl(option('site_url'));

        if ($this->skin != '') {
            $textures['textures']['SKIN'] = [
                'url' => url("textures/{$this->skin}"),
            ];

            if ($this->model == 'slim') {
                $textures['textures']['SKIN']['metadata'] = ['model' => 'slim'];
            }
        } elseif (
            Schema::hasTable('mojang_verifications')
            && DB::table('mojang_verifications')->where('uuid', $this->uuid)->exists()
        ) {
            // 如果该角色没有在皮肤站设置皮肤，就从 Mojang 获取。
            $skin = $this->fetchProfileFromMojang('SKIN');
            if ($skin) {
                $textures['textures']['SKIN'] = $skin;
            }
        }

        if ($this->cape != '') {
            $textures['textures']['CAPE'] = [
                'url' => url("textures/{$this->cape}"),
            ];
        } elseif (
            Schema::hasTable('mojang_verifications')
            && DB::table('mojang_verifications')->where('uuid', $this->uuid)->exists()
        ) {
            // 如果该角色没有在皮肤站设置披风，就从 Mojang 获取。
            $cape = $this->fetchProfileFromMojang('CAPE');
            if ($cape) {
                $textures['textures']['CAPE'] = $cape;
            }
        }

        $result = [
            'id' => str_replace('-', '', $this->uuid),
            'name' => $this->name,
            'properties' => [
                [
                    'name' => 'textures',
                    'value' => base64_encode(
                        json_encode($textures, JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT)
                    ),
                ],
                [
                    'name' => 'uploadableTextures',
                    'value' => 'skin,cape',
                ],
            ],
        ];

        if ($unsigned === false) {
            // 给每个 properties 签名
            foreach ($result['properties'] as &$prop) {
                $signature = $this->sign($prop['value'], $key);

                $prop['signature'] = base64_encode($signature);
            }

            unset($prop);
            unset($key);
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

        if ($result) {
            return $result->uuid;
        }

        if (option('ygg_uuid_algorithm') === 'v3') {
            $uuid = static::generateUuidV3($name);
        } else {
            $uuid = Uuid::uuid4()->getHex()->toString();
        }
        DB::table('uuid')->insert(['name' => $name, 'uuid' => $uuid]);
        Log::channel('ygg')->info("New uuid [$uuid] allocated to player [$name]");

        return $uuid;
    }

    public static function createFromUuid($uuid)
    {
        $result = DB::table('uuid')->where('uuid', $uuid)->first();

        if ($result && ($player = Player::where('name', $result->name)->first())) {
            return static::createFromPlayer($player);
        }
    }

    public static function createFromPlayer(Player $player)
    {
        $profile = new static();
        $model = 'default';
        if ($t = Texture::find($player->tid_skin)) {
            $model = $t->type == 'steve' ? 'default' : 'slim';
        }

        $profile->uuid = static::getUuidFromName($player->name);
        $profile->name = $player->name;
        $profile->model = $model;
        $profile->player = $player;
        $profile->skin = optional($player->skin)->hash;
        $profile->cape = optional($player->cape)->hash;

        return $profile;
    }

    public static function generateUuidV3(string $name): string
    {
        // @see https://gist.github.com/games647/2b6a00a8fc21fd3b88375f03c9e2e603
        $data = hex2bin(md5('OfflinePlayer:'.$name));
        $data[6] = chr(ord($data[6]) & 0x0F | 0x30);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        return bin2hex($data);
    }

    protected function fetchProfileFromMojang($type)
    {
        $type = strtoupper($type);
        $profile = Cache::get('mojang_profile_'.$this->uuid, function () {
            try {
                $response = Http::get('https://sessionserver.mojang.com/session/minecraft/profile/'.$this->uuid);
                if ($response->ok()) {
                    $body = $response->json();
                    Cache::put('mojang_profile_'.$this->uuid, $body, 300);

                    return $body;
                } else {
                    return null;
                }
            } catch (\Exception $e) {
                return null;
            }
        });

        if (!$profile) {
            return null;
        }
        $property = Arr::first($profile['properties'], function ($item) {
            return $item['name'] === 'textures';
        });
        if (!$property) {
            return null;
        }

        return Arr::get(json_decode(base64_decode($property['value']), true)['textures'], $type);
    }
}
