<?php

namespace Yggdrasil\Models;

use DB;
use App\Models\Player;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Exceptions\NotFoundException;
use Yggdrasil\Exceptions\IllegalArgumentException;

class Profile
{
    protected $uuid = "";

    protected $name = "";

    protected $player = null;

    protected $model = "default";

    protected $skin = "";

    protected $cape = "";

    protected $signature = null;

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getUuidWithoutDashes()
    {
        return UUID::import($this->uuid)->clearDashes();
    }

    public function setUuid($uuid)
    {
        return ($this->uuid = $uuid);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        return ($this->name = $name);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel($model)
    {
        if ($model != "default" && $model != "slim") {
            throw new \InvalidArgumentException('The model must be one of "default" or "slim"', 1);
        }

        return ($this->model = $model);
    }

    public function getPlayer()
    {
        return $this->player;
    }

    public function setPlayer(Player $player)
    {
        return ($this->player = $player);
    }

    public function getSkin()
    {
        return $this->skin;
    }

    public function setSkin($skin)
    {
        return ($this->skin = $skin);
    }

    public function getCape()
    {
        return $this->cape;
    }

    public function setCape($cape)
    {
        return ($this->cape = $cape);
    }

    public function sign($data, $key)
    {
        openssl_sign($data, $sign, $key);

        return $sign;
    }

    public function serialize($unsigned = true)
    {
        $privateKeyPath = plugin('yggdrasil-api')->getPath().'/key.pem';

        if (app('request')->get('unsigned') === 'false' || file_exists($privateKeyPath)) {
            $unsigned = false;
        }

        $textures = [
            'timestamp' => round(microtime(true) * 1000),
            'profileId' => UUID::import($this->uuid)->string,
            'profileName' => $this->name,
            'isPublic' => true,
            'textures' => []
        ];

        if ($unsigned === false) {
            // Load private key
            if (file_exists($privateKeyPath)) {
                $privateKeyContent = file_get_contents($privateKeyPath);

                $key = openssl_pkey_get_private($privateKeyContent);

                if (! $key) {
                    throw new IllegalArgumentException('无效的 RSA 私钥');
                }

                $textures['signatureRequired'] = true;
            } else {
                throw new IllegalArgumentException('RSA 私钥不存在');
            }
        }

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
            'id' => $this->getUuidWithoutDashes(),
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
            // Sign every properties
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
            $result = UUID::generate()->clearDashes();
            DB::table('uuid')->insert(['name' => $name, 'uuid' => $result]);
        } else {
            $result = $result->uuid;
        }

        return $result;
    }

    public static function createFromUuid($uuid)
    {
        $result = DB::table('uuid')->where('uuid', $uuid)->first();

        if (! $result) {
            throw new NotFoundException('No such UUID.', 1);
        }

        $player = Player::where('player_name', $result->name)->first();

        if (! $player) {
            throw new NotFoundException('The player associated with this UUID does not exist', 1);
        }

        return static::createFromPlayer($player);
    }

    public static function createFromPlayer(Player $player)
    {
        $profile = new static();

        $profile->setUuid(static::getUuidFromName($player->player_name));
        $profile->setName($player->player_name);
        $profile->setModel($player->getPreference());
        $profile->setPlayer($player);
        $profile->setSkin($player->getTexture('skin'));
        $profile->setCape($player->getTexture('cape'));

        return $profile;
    }
}
