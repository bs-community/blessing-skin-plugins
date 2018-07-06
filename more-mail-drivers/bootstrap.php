<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $config = require __DIR__.'/config/mail.php';

    config(['services' => $config]);
    config(['mail.sendmail' => $config['sendmail']]);
};
