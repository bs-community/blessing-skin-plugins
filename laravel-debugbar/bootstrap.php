<?php

return function () {
    app()->register(Barryvdh\Debugbar\ServiceProvider::class);
    app()->alias('Debugbar', Barryvdh\Debugbar\Facade::class);
};
