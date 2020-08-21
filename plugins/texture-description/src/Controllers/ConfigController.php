<?php

namespace Blessing\TextureDescription\Controllers;

use App\Services\Facades\Option;
use App\Services\OptionForm;
use Illuminate\Routing\Controller;

class ConfigController extends Controller
{
    public function render()
    {
        $form = Option::form(
            'texture-description',
            trans('Blessing\TextureDescription::config.general.title'),
            function (OptionForm $form) {
                $form->text(
                    'textures_description_limit',
                    trans('Blessing\TextureDescription::config.general.textures_description_limit.title')
                );
            }
        )->handle();

        return view('Blessing\TextureDescription::config', ['form' => $form]);
    }
}
