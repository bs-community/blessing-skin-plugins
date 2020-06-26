<?php

use App\Events\RenderingHeader;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(RenderingHeader::class, function ($event) {
        $html = view('Blessing\Gtag::snippet', [
            'ga_id' => option('ga_id'),
        ])->render();
        $event->addContent($html);
    });
};
