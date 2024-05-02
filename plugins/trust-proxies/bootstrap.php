<?php

return function () {
    $kernel = app()->make('Illuminate\Contracts\Http\Kernel');
    $kernel->pushMiddleware(GPlane\TrustProxies\TrustProxies::class);
};
