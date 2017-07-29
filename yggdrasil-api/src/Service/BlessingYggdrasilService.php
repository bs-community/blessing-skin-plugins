<?php

namespace Yggdrasil\Service;

use Cache;
use App\Models\User;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Models\Token;
use Yggdrasil\Models\Profile;
use Yggdrasil\Exceptions\NotFoundException;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class BlessingYggdrasilService implements YggdrasilServiceInterface
{

    public function __construct()
    {
        # code...
    }

    public function authenticate($username, $password, $clientToken)
    {
        
    }
}
