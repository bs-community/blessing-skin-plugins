<?php

namespace Yggdrasil\Service;

interface YggdrasilServiceInterface
{
    public function authenticate($identification, $password, $clientToken);
    public function refresh($clientToken, $accessToken);
    public function validate($clientToken, $accessToken);
    public function signout($identification, $password);
    public function invalidate($accessToken);
    public function joinServer($accessToken, $selectedProfile, $serverId);
    public function hasJoinedServer($name, $serverId, $ip);
}
