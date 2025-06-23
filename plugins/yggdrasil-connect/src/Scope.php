<?php

namespace LittleSkin\YggdrasilConnect;

class Scope
{
    public const OPENID = 'openid';
    public const PROFILE = 'profile';
    public const EMAIL = 'email';
    public const OFFLINE_ACCESS = 'offline_access';
    public const PROFILE_READ = 'Yggdrasil.PlayerProfiles.Read';
    public const PROFILE_SELECT = 'Yggdrasil.PlayerProfiles.Select';
    public const SERVER_JOIN = 'Yggdrasil.Server.Join';

    public const OIDC_SCOPES = [
        self::OPENID,
        self::PROFILE,
        self::EMAIL,
        self::OFFLINE_ACCESS,
        self::PROFILE_READ,
        self::PROFILE_SELECT,
        self::SERVER_JOIN,
    ];
}
