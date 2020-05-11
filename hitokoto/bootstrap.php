<?php

use App\Services\Hook;
use App\Services\Plugin;

return function (Plugin $plugin) {
    Hook::addScriptFileToPage(
        $plugin->assets('hitokoto.js'),
        ['user', 'user/*', 'admin', 'admin/*']
    );
};
