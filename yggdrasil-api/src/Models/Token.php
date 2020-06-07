<?php

namespace Yggdrasil\Models;

use Cache;

class Token
{
    public $owner;
    public $profileId = '';
    public $createdAt;
    public $clientToken;
    public $accessToken;

    public function __construct($clientToken, $accessToken)
    {
        $this->clientToken = $clientToken;
        $this->accessToken = $accessToken;
        $this->createdAt = time();
    }

    public function isValid()
    {
        return (time() - $this->createdAt - option('ygg_token_expire_1')) < 0;
    }

    public function isRefreshable()
    {
        return (time() - $this->createdAt - option('ygg_token_expire_2')) < 0;
    }

    // 这个方法只是为了方便写日志
    public function serialize()
    {
        return [
            'clientToken' => $this->clientToken,
            'accessToken' => $this->accessToken,
            'owner' => $this->owner,
            'createdAt' => $this->createdAt,
        ];
    }

    /**
     * Lookup the specified token, or null if the token does not exist or has expired.
     * The returned token is guaranteed to be refreshable, but it may not be valid.
     */
    public static function lookup(string $accessToken)
    {
        $token = Cache::get("TOKEN_$accessToken");
        if ($token) {
            if ($token->isRefreshable()) {
                return $token;
            } else {
                Cache::forget("TOKEN_$accessToken");
            }
        }

        return null;
    }
}
