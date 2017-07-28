<?php

namespace Yggdrasil\Models;

use DB;
use App\Models\Player;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Exceptions\NotFoundException;

class Token
{
    protected $ownerUuid;

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

    public function getOwnerUuid()
    {
        return $this->ownerUuid;
    }

    public function setOwnerUuid($uuid)
    {
        return ($this->ownerUuid = $uuid);
    }
}
