<?php

namespace Blessing\CDN;

use Illuminate\Http\Response;

class ModifyStaticFileUrl
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        if ($response instanceof Response && option('cdn_address')) {
            $response->setContent(str_replace(
                url('public'),
                option('cdn_address'),
                $response->getContent()
            ));
        }

        return $response;
    }
}
