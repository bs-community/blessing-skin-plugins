<?php

use App\Events\RenderingHeader;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(RenderingHeader::class, function ($event) {
        $html = view('BigCake\googleadsense::snippet', [
            'client_id' => option('client_id'),
        ])->render();
        $event->addContent($html);
    });
};
