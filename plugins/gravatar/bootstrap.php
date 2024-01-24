<?php

use Blessing\Filter;

return function (Filter $filter) {
    $filter->add('user_avatar', function ($url, $user) {
        if ($user->avatar !== 0) {
            return $url;
        }

        $hashed = md5(strtolower(trim($user->email)));

        return "https://cravatar.cn/avatar/$hashed";
    });
};
