<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;

return function (Dispatcher $events, Request $request) {
    if($request->has('eruda')) {
        $events->listen(App\Events\RenderingHeader::class, function($event) {
            $event->addContent('<script src="https://cdn.jsdelivr.net/npm/eruda"></script><script>eruda.init();</script>');
        });
    }
}

?>