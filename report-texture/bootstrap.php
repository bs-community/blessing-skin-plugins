<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

require __DIR__.'/src/helpers.php';

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

    report_init_options();

    if (request()->is('skinlib/show/*')) {
        $events->listen(App\Events\RenderingFooter::class, function ($event) {
            $value = filter_var(option('reporter_score_modification'), FILTER_VALIDATE_INT);
            $value = $value === false ? 0 : $value;

            $event->addContent("<script>blessing.reporterScoreModification = $value;</script>");
            $event->addContent('<script src="'.plugin('report-texture')->assets('assets/dist/report.js').'"></script>');
        });
    }

    if (option('content_policy')) {
        Hook::addScriptFileToPage(
            plugin('report-texture')->assets('assets/dist/content-policy.js'),
            ['skinlib/upload']
        );
    }

    Hook::registerPluginTransScripts('report-texture');

    $index = (plugin('data-integration') && plugin('data-integration')->isEnabled()) ? 2 : 3;

    Hook::addMenuItem('user', $index, [
        'title' => 'ReportTexture::general.menu.user',
        'link'  => 'user/report',
        'icon'  => 'fa-flag'
    ]);

    Hook::addMenuItem('admin', 3, [
        'title' => 'ReportTexture::general.menu.admin',
        'link'  => 'admin/reports',
        'icon'  => 'fa-flag'
    ]);

    Hook::addRoute(function ($router) {
        $router->group([
            'middleware' => ['web', 'auth'],
            'namespace'  => 'ReportTexture',
        ], function ($router) {
            $router->get('user/report', 'ReportController@showMyReports');
            $router->get('skinlib/content-policy', 'ReportController@showContentPolicy');
            $router->post('skinlib/report', 'ReportController@report');

            $router->get('admin/reports', 'ReportController@showReportsManage')->middleware(['admin']);
            $router->post('admin/reports', 'ReportController@handleReports')->middleware(['admin']);
        });
    });
};
