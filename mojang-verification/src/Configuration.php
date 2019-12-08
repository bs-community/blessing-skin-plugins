<?php

namespace GPlane\Mojang;

use Option;

class Configuration
{
    public function render()
    {
        $form = Option::form(
            'mojang_verification',
            trans('GPlane\Mojang::mojang-verification.config.score_config'),
            function ($form) {
                $form->text(
                    'mojang_verification_score_award',
                    trans('GPlane\Mojang::mojang-verification.config.score_award')
                )->placeholder(trans('GPlane\Mojang::mojang-verification.config.default'))
                    ->description(trans('GPlane\Mojang::mojang-verification.config.description'));
        })->handle();

        return view('GPlane\Mojang::config', compact('form'));
    }
}
