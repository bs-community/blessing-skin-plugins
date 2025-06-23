<?php

namespace LittleSkin\YggdrasilConnect\Models;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;

class Profile
{
    public string $uuid;
    public string $name;
    public Player $player;
    public string $model = 'default';
    public ?string $skin;
    public ?string $cape;

    public function sign($data, $key)
    {
        openssl_sign($data, $sign, $key);

        return $sign;
    }

    public function serialize($unsigned = null): string
    {
        // 如果没显式指定 `unsigned` 参数就从 URL 中推断
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
                throw new IllegalArgumentException(trans('LittleSkin\YggdrasilConnect::config.rsa.invalid'));
            }

            $textures['signatureRequired'] = true;
        }

        // 避免 BungeeCord 服务器上可能出现无法加载材质的 Bug
        app('url')->forceRootUrl(option('site_url'));

        if ($this->skin != '') {
            $textures['textures']['SKIN'] = [
                'url' => url('textures', $this->skin),
            ];

            if ($this->model == 'slim') {
                $textures['textures']['SKIN']['metadata'] = ['model' => 'slim'];
            }
        }

        if ($this->cape != '') {
            $textures['textures']['CAPE'] = [
                'url' => url('textures', $this->cape),
            ];
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

    public function __toString(): string
    {
        return $this->serialize();
    }

    public static function getUuidFromName($name): ?string
    {
        if ($player = Player::where('name', $name)->first()) {
            return static::getUuidFromPlayer($player);
        }

        return null;
    }

    public static function getUuidFromPlayer(Player $player): string
    {
        if ($uuid = UUID::where('pid', $player->pid)->first()) {
            return $uuid->uuid;
        }

        $uuid = UUID::create(['pid' => $player->pid]);
        $name = $uuid->player->name;
        Log::channel('ygg')->info("New uuid [$uuid->uuid] allocated to player [$name]");

        return $uuid->uuid;
    }

    public static function createFromUuid(string $uuid): ?Profile
    {
        $result = UUID::where('uuid', $uuid)->first();

        if (optional($result)->player) {
            $profile = new static();
            $model = 'default';
            if ($t = Texture::find($result->player->tid_skin)) {
                $model = $t->type == 'steve' ? 'default' : 'slim';
            }

            $profile->uuid = static::getUuidFromPlayer($result->player);
            $profile->name = $result->player->name;
            $profile->model = $model;
            $profile->player = $result->player;
            $profile->skin = optional($result->player->skin)->hash;
            $profile->cape = optional($result->player->cape)->hash;

            return $profile;
        } else {
            UUID::where('uuid', $uuid)->delete();
        }

        return null;
    }

    public static function createFromPlayer(Player $player): Profile
    {
        $uuid = static::getUuidFromPlayer($player);

        return static::createFromUuid($uuid);
    }

    public static function getAvailableProfiles(User $user): array
    {
        $profiles = [];

        foreach ($user->players as $player) {
            $uuid = Profile::getUuidFromPlayer($player);

            $profiles[] = [
                'id' => $uuid,
                'name' => $player->name,
            ];
        }

        return $profiles;
    }
}
