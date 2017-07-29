<?php

namespace Yggdrasil\Models;

use DB;
use App\Models\Player;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Exceptions\NotFoundException;

class Token
{
    protected $owner;

    protected $clientToken;

    protected $accessToken;

    public function __construct($clientToken, $accessToken)
    {
        $this->clientToken = $clientToken;
        $this->accessToken = $accessToken;
    }

    public function getClientToken()
    {
        return $this->clientToken;
    }

    public function setClientToken($token)
    {
        return ($this->clientToken = $token);
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken($token)
    {
        return ($this->accessToken = $token);
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner($uuid)
    {
        return ($this->owner = $uuid);
    }

    public function serialize()
    {
        return [
            'clientToken' => $this->clientToken,
            'accessToken' => $this->accessToken,
            'owner' => $this->owner
        ];
    }
}
