<?php

use Blessing\Filter;
use Vectorface\Whip\Whip;

return function (Filter $filter) {
    $filter->add('client_ip', function () {
        $whip = new Whip(Whip::REMOTE_ADDR);

        return $whip->getValidIpAddress();
    });
};
