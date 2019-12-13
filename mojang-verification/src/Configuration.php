<?php

namespace GPlane\Mojang;

use Option;

class Configuration
{
    public function render()
    {
        $form = Option::form(
            'mojang_verification',
            trans('GPlane\Mojang::config.score_config'),
            function ($form) {
                $form->text(
                    'mojang_verification_score_award',
                    trans('GPlane\Mojang::config.score_award')
                )->placeholder(trans('GPlane\Mojang::config.default'))
                    ->description(trans('GPlane\Mojang::config.description'));
        })->handle();

        return view('GPlane\Mojang::config', compact('form'));
    }
}
