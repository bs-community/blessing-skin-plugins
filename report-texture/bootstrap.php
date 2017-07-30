<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

require __DIR__.'/src/common.php';

return function (Dispatcher $events) {

    // Create tables
    if (! Schema::hasTable('reports')) {
        Schema::create('reports', function ($table) {
            $table->increments('id');
            $table->integer('tid');
            $table->integer('uploader');
            $table->integer('reporter');
            $table->longText('reason');
            // PENDING, RESOLVED, REJECTED
            $table->integer('status');
            $table->dateTime('report_at');
        });
    }

    Hook::addScriptFileToPage(plugin('report-texture')->assets('assets/report.js'), [
        'skinlib/show/*',
        'admin/reports*'
    ]);

    // Hook::registerPluginTransScripts('example-plugin');

    Hook::addMenuItem('user', 3, [
        'title' => '我的举报',
        'link'  => 'user/report',
        'icon'  => 'fa-flag'
    ]);

    Hook::addMenuItem('admin', 3, [
        'title' => '举报管理',
        'link'  => 'admin/reports',
        'icon'  => 'fa-flag'
    ]);

    Hook::addRoute(function ($router) {
        $router->group([
            'middleware' => ['web', 'auth'],
            'namespace'  => 'Blessing\Report',
        ], function ($router) {
            $router->get('user/report', 'ReportController@showMyReports');
            $router->post('skinlib/report', 'ReportController@report');

            $router->get('admin/reports', 'ReportController@showReportsManage')->middleware(['admin']);
            $router->post('admin/reports', 'ReportController@handleReports')->middleware(['admin']);
        });
    });
};
