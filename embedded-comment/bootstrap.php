<?php

use App\Services\Filter;

return function (Filter $filter) {
    View::composer('Blessing\EmbeddedComment::comment', function ($view) {
        $view->with('url', request()->url());
        $view->with('code', option('comment_script'));
    });

    $filter->add('grid:skinlib.show', function ($grid) {
        $grid['layout'][] = ['md-12'];
        $grid['widgets'][] = [['Blessing\EmbeddedComment::comment']];

        return $grid;
    });
};
