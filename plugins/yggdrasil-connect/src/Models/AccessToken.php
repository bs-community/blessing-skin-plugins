<?php

namespace LittleSkin\YggdrasilConnect\Models;

use App\Models\User as BaseUser;
use App\Services\Facades\Option;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\Token;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\ForbiddenOperationException;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;
use LittleSkin\YggdrasilConnect\Models\User;
use LittleSkin\YggdrasilConnect\Scope;

class AccessToken
{
    public     string                 $jwt;
    protected  Token                  $passportToken;
    protected  TokenRepository        $tokenRepository;
    protected  RefreshTokenRepository $refreshTokenRepository;
    protected  JWT\Configuration      $jwtConfig;
    protected  JWT\Builder            $builder;
    protected  JWT\Parser             $parser;
    protected  JWT\Validator          $validator;
    protected  JWT\Token\Plain        $jwtDecoded;
    public     ?User                  $owner = null;
    public     ?string                $selectedProfile = null;

    public function __construct(string $jwt)
    {
        $this->tokenRepository = app(TokenRepository::class);
        $this->refreshTokenRepository = app(RefreshTokenRepository::class);

        $this->jwtConfig = JWT\Configuration::forAsymmetricSigner(
            new JWT\Signer\Rsa\Sha256(),
            JWT\Signer\Key\InMemory::file(storage_path('oauth-private.key')),
            JWT\Signer\Key\InMemory::file(storage_path('oauth-public.key'))
        );

        $this->builder = $this->jwtConfig->builder();
        $this->parser = $this->jwtConfig->parser();
        $this->validator = $this->jwtConfig->validator();

        // Constructor 里只做合法性校验，是否有效放到 isValid() 和 canJoinServer() 里来做

        try {
            $this->jwtDecoded = $this->parser->parse($jwt);
            $this->validator->assert(
                $this->jwtDecoded,
                new JWT\Validation\Constraint\SignedWith($this->jwtConfig->signer(), $this->jwtConfig->verificationKey())
            );
        } catch (JWT\Exception) {
            throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.token.invalid'));
        }

        // 从数据库里查找 Access Token
        if (!$this->passportToken = $this->tokenRepository->find($this->jwtDecoded->claims()->get('jti'))) {
            throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.token.invalid'));
        }

        if ($this->passportToken->client_id != $this->jwtDecoded->claims()->get('aud')[0] || $this->passportToken->user_id != $this->jwtDecoded->claims()->get('sub')) {
            throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.token.invalid'));
        }

        $this->owner = User::where('uid', $this->passportToken->user_id)->first();
        if (empty($this->owner) || $this->owner->permission == User::BANNED) {
            throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.token.invalid'));
        }

        $this->owner->withAccessToken($this->passportToken);
        $this->owner->withYggdrasilToken($this);
        $this->selectedProfile = $this->jwtDecoded->claims()->get('selectedProfile');
        $this->jwt = $jwt;
    }

    static public function create(BaseUser $user): AccessToken
    {
        $token = $user->createToken('Yggdrasil Connect', [Scope::PROFILE_SELECT, Scope::SERVER_JOIN])->accessToken;
        return new AccessToken($token);
    }

    public function isRevoked(): bool
    {
        return $this->passportToken->revoked;
    }

    public function isExpired1(): bool
    {
        if (!$this->validator->validate(
            $this->jwtDecoded,
            new JWT\Validation\Constraint\ValidAt(new SystemClock(new \DateTimeZone(date_default_timezone_get())))
        )) {
            return true;
        }

        if ($this->passportToken->expires_at->isPast()) {
            return true;
        }

        return false;
    }

    public function isExpired2(): bool
    {
        return $this->isExpired1() && $this->passportToken->created_at->addSeconds(Option::get('ygg_token_expire_2'))->isPast();
    }

    private function hasScopesToJoinServer(): bool
    {
        return $this->can(Scope::PROFILE_READ) || $this->passportToken->can(Scope::PROFILE_SELECT);
    }

    private function isSelectedProfileValid(): bool
    {

        $profile = Profile::createFromUuid($this->selectedProfile);
        if (empty($profile)) {
            return false;
        }

        if ($profile->player->uid !== $this->owner->uid) {
            return false;
        }

        return true;
    }

    // 这里只是检查 Access Token 是否在有效期内，以及绑定到的角色是否有效，供 UserInfo Endpoint 使用
    // Yggdrasil API 中检查 Access Token 是否有效实际上是在检查 Access Token 是否能进入服务器，在 canJoinServer() 中实现
    public function isValid(): bool
    {
        // 如果 Access Token 已经被吊销，或者过期了（暂时过期）
        if ($this->isRevoked() || $this->isExpired1()) {
            return false;
        }

        if ($this->can(Scope::PROFILE_SELECT)) {
            if (empty($this->selectedProfile) || !$this->isSelectedProfileValid()) {
                return false;
            }

            $nameChangedAt = Cache::get("player-renamed-$this->selectedProfile");
            if (optional($nameChangedAt)->greaterThan($this->passportToken->created_at)) {
                // 如果 Access Token 创建时间早于角色改名时间
                return false;
            }
        }

        return true;
    }

    public function isRefreshable(): bool
    {
        // 如果是由 Janus 生成的
        // Janus 生成 Access Token 应该使用 Refresh Token 来刷新
        if ($this->jwtDecoded->claims()->has('iss')) {
            return false;
        }

        // 如果 Access Token 已经被吊销，或者过期了（永久过期）
        if ($this->isRevoked() || $this->isExpired2()) {
            return false;
        }

        // 如果 Access Token 已绑定到角色，但角色不属于用户
        if (!empty($this->selectedProfile) && !$this->isSelectedProfileValid()) {
            return false;
        }

        // 如果 Access Token 权限不足
        if (!$this->hasScopesToJoinServer()) {
            return false;
        }

        return true;
    }

    public function refresh(?string $uuid = null): AccessToken
    {

        if (!$this->isRefreshable()) {
            throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.token.invalid'));
        }

        // 尝试直接刷新一个未绑定角色的 Access Token
        if (empty($uuid) && empty($this->selectedProfile)) {
            throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::exceptions.token.no-selected-profile'));
        }

        if (!empty($uuid)) {
            // 指定的角色与 Access Token 绑定的角色不一致
            if (!empty($this->selectedProfile) && $this->selectedProfile !== $uuid) {
                throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::exceptions.player.not-match'));
            }

            // 检查角色是否存在、是否属于用户
            $profile = Profile::createFromUuid($uuid);
            if (empty($profile) || $profile->uuid !== $uuid || $profile->player->uid !== $this->owner->uid) {
                throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.player.not-exist'));
            }
        }

        // isRefreshable() 里已经检查过了 Access Token 绑定的角色是否有效

        $selectedProfile = $this->selectedProfile ?? $uuid;

        $newToken = AccessToken::create($this->owner);

        // Laravel Passport 不支持为 Access Token 添加自定义声明，干脆直接解码后再编码
        /** @var JWT\Token\Plain */
        $newTokenDecoded = $this->parser->parse($newToken->jwt);

        // RFC 7519 中规定的声明只能通过这些方法来设置
        $this->builder->canOnlyBeUsedAfter($newTokenDecoded->claims()->get('nbf'))
            ->expiresAt($newTokenDecoded->claims()->get('exp'))
            ->identifiedBy($newTokenDecoded->claims()->get('jti'))
            ->issuedAt($newTokenDecoded->claims()->get('iat'))
            ->permittedFor($newTokenDecoded->claims()->get('aud')[0])
            ->relatedTo($newTokenDecoded->claims()->get('sub'));

        // 其他声明通过 withClaim() 方法设置
        foreach ($newTokenDecoded->claims()->all() as $key => $value) {
            if (!in_array($key, JWT\Token\RegisteredClaims::ALL)) {
                $this->builder->withClaim($key, $value);
            }
        }

        // 设置选中的角色
        $this->builder->withClaim('selectedProfile', $selectedProfile);

        return new AccessToken($this->builder->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())->toString());
    }

    public function revoke(): void
    {
        $this->passportToken->revoke();
        $this->refreshTokenRepository->revokeRefreshTokensByAccessTokenId($this->passportToken->id);
    }

    public function canJoinServer(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if (!$this->hasScopesToJoinServer()) {
            return false;
        }

        return true;
    }

    static public function revokeAllForUser(BaseUser $user): void
    {
        $tokens = Token::where('user_id', $user->uid)->where([
            ['user_id', $user->uid],
            ['client_id', env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID')],
            ['revoked', false],
            ['expires_at', '>', now()],
        ])->get();

        foreach ($tokens as $token) {
            $token->revoke();
        }
    }

    public function __call($method, $paramaters)
    {
        return $this->passportToken->{$method}(...$paramaters);
    }

    public function __get($name)
    {
        return $this->passportToken->{$name};
    }

    public function __isset($name)
    {
        return isset($this->passportToken->{$name});
    }
}
