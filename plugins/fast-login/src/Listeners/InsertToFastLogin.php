<?php

namespace Blessing\FastLogin;

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class InsertToFastLogin
{
    public function handle($user, $profile)
    {
        $uuid = Uuid::fromString($profile['id']);

        DB::connection('fast-login')
            ->table('premium')
            ->insert([
                'UUID' => $uuid->toString(),
                'Name' => $profile['name'],
                'Premium' => true,
                'LastIp' => '0.0.0.0',
            ]);
    }
}
