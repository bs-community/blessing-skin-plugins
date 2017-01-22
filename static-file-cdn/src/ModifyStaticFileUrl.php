<?php
/**
 * @Author: printempw
 * @Date:   2017-01-18 13:08:04
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-18 17:27:18
 */

namespace Blessing\CDN;

use Illuminate\Http\Response;

class ModifyStaticFileUrl
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        if ($response instanceof Response && option('cdn_address')) {
            $response->setContent(str_replace(
                url('resources/assets'),
                option('cdn_address'),
                $response->getContent()
            ));
        }

        return $response;
    }
}
