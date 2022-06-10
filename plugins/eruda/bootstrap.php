<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;

return function (Dispatcher $events, Request $request) {
    if ($request->has('eruda')) {
        Hook::addScriptFileToPage('https://unpkg.com/eruda');
        $events->listen(App\Events\RenderingFooter::class, function ($event) {
            $event->addContent('<script>eruda.init();</script>');
        });
    }
};
