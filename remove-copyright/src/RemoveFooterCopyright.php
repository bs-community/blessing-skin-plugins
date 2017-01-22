<?php
/**
 * @Author: printempw
 * @Date:   2017-01-20 21:38:39
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-21 12:57:20
 */

namespace Blessing\RemoveCopy;

use Illuminate\Http\Response;

class RemoveFooterCopyright
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        if ($response instanceof Response) {
            $response->setContent(preg_replace(
                '/<div id="copyright-text".*>(([\s\S]*))<\/div>([\s\S]*)<!-- Default to the left -->/', '',
                $response->getContent()
            ));
        }

        return $response;
    }
}
