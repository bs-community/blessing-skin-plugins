<?php

namespace Blessing\ConfigGenerator;

use Option;

class Configuration
{
    public function render()
    {
        $form = Option::form(
            'config_generator',
            trans('Blessing\ConfigGenerator::options.title'),
            function ($form) {
                $form->textarea(
                    'config_generator_intro_'.config('app.locale'),
                    trans('Blessing\ConfigGenerator::options.custom_intro.title')
                )->rows(5)
                ->description(trans('Blessing\ConfigGenerator::options.custom_intro.description'));

                $form->select(
                    'csl_first',
                    trans('Blessing\ConfigGenerator::options.csl_first.title')
                )->option('mojang', 'Mojang')
                ->option('self', trans('Blessing\ConfigGenerator::options.csl_first.self'));
            }
        )->handle();

        return view('Blessing\ConfigGenerator::config', compact('form'));
    }
}
