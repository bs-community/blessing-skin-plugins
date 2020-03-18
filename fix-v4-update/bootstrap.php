<?php

return function () {
    app()->bind(\App\Services\PackageManager::class, function () {
        return new \Blessing\FixV4Update\PackageManager();
    });
};
