<?php

namespace Yggdrasil\Models;

use DB;
use App\Models\Player;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Exceptions\NotFoundException;

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

    public function sign()
    {
        return 'signature';
    }

    public function serialize($unsigned = true)
    {
        $textures = [
            'timestamp' => round(microtime(true) * 1000),
            'profileId' => $this->getUuidWithoutDashes(),
            'profileName' => $this->name,
            'textures' => []
        ];

        if ($this->skin != "") {
            $textures['textures']['SKIN'] = [
                'url' => url("textures/{$this->skin}")
            ];
        }

        if ($this->cape != "") {
            $textures['textures']['CAPE'] = [
                'url' => url("textures/{$this->cape}")
            ];
        }

        if (! $unsigned) {
            $textures['signatureRequired'] = true;
            $signature = $this->sign($textures);
        }

        $result = [
            'id' => $this->getUuidWithoutDashes(),
            'name' => $this->name,
            'properties' => [
                [
                    'name' => 'textures',
                    'value' => base64_encode(json_encode($textures))
                ]
            ]
        ];

        if (isset($signature)) {
            $result['properties'][0]['signature'] = $signature;
        }

        if ($this->model == "slim") {
            $result['properties'][0]['metadata'] = ['model' => 'slim'];
        }

        return json_encode($result);
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
