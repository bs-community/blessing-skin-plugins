<?php

namespace Yggdrasil\Models;

use Cache;
use Lcobucci\JWT;
use Ramsey\Uuid\Uuid;

class Token
{
    public $owner;
    public $profileId = '';
    public $createdAt;
    public $clientToken;
    public $accessToken;

    public function __construct($clientToken = '', $accessToken = '')
    {
        $this->clientToken = $clientToken;
        $this->accessToken = $accessToken;
        $this->createdAt = time();
    }

    public function isValid()
    {
        if (str_contains($this->accessToken, '.')) {
            $jwtConfig = JWT\Configuration::forSymmetricSigner(
                new JWT\Signer\Hmac\Sha256(),
                JWT\Signer\Key\InMemory::plainText(config('jwt.secret', ''))
            );
            $jwtConfig->setValidationConstraints(
                new JWT\Validation\Constraint\IssuedBy('Yggdrasil-Auth'),
                new JWT\Validation\Constraint\RelatedTo(
                    Uuid::uuid5(Uuid::NAMESPACE_DNS, $this->owner)->getHex()->toString()
                )
            );
            $token = $jwtConfig->parser()->parse($this->accessToken);

            return $jwtConfig->validator()->validate($token, ...$jwtConfig->validationConstraints());
        } else {
            // fallback for legacy UUID-format accessToken
            return (time() - $this->createdAt - option('ygg_token_expire_1')) < 0;
        }
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
     * Search the specified token, or null if the token does not exist or has expired.
     * The returned token is guaranteed to be refreshable, but it may not be valid.
     */
    public static function find(string $accessToken)
    {
        $token = Cache::get("yggdrasil-token-$accessToken");
        if ($token) {
            if ($token->isRefreshable()) {
                return $token;
            } else {
                Cache::forget("yggdrasil-token-$accessToken");
            }
        }

        return null;
    }
}
