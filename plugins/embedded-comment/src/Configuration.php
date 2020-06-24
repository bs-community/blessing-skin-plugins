<?php

namespace Blessing\EmbeddedComment;

use Option;

class Configuration
{
    public function render()
    {
        $title = trans('Blessing\EmbeddedComment::config.title');

        $form = Option::form('embedded_comment', $title, function ($form) use ($title) {
            $form->textarea('comment_script', $title)
                ->rows(6)
                ->description(trans('Blessing\EmbeddedComment::config.description'));
        })->handle();

        return view('Blessing\EmbeddedComment::config', compact('form'));
    }
}
