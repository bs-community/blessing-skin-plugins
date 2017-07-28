<?php

namespace Yggdrasil\Service;

interface YggdrasilServiceInterface
{
    public function authenticate($username, $password, $accessToken);
}
