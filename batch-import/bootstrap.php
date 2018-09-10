<?php

use App\Services\Hook;

return function () {
    Hook::addMenuItem('admin', 3, [
        'title' => '批量导入',
        'link'  => 'admin/batch-import',
        'icon'  => 'fa-truck',
    ]);

    Hook::addRoute(function ($router) {
        $router->group([
            'prefix'     => 'admin/batch-import',
            'middleware' => ['web', 'auth', 'admin'],
            'namespace'  => 'BatchImport',
        ], function ($router) {
            $router->get('', 'BatchImportController@index');
            $router->post('check-dir', 'BatchImportController@checkImportDir');
            $router->post('chunk-import', 'BatchImportController@chunkImport');
        });
    });
};
