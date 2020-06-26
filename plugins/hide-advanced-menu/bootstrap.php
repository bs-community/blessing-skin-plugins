<?php

namespace Blessing\HideAdvancedMenu;

use Blessing\Filter;

return function (Filter $filter) {
    $filter->add('side_menu', function ($menu, $type) {
        if ($type !== 'user') {
            return $menu;
        }

        return array_filter($menu, function ($item) {
            return $item['title'] !== 'general.developer';
        });
    });
};
