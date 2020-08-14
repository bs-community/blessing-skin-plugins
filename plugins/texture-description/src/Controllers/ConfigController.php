<?php

namespace Blessing\TextureDesc\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Facades\Option;
use App\Services\OptionForm;

class ConfigController extends Controller
{
    public function render()
    {
        $form = Option::form(
            'texture-description',
            trans('Blessing\TextureDesc::config.general.title'),
            function (OptionForm $form) {
                $form->text(
                    'textures_desc_limit',
                    trans('Blessing\TextureDesc::config.general.textures_desc_limit.title')
                );
            }
        )->handle();

        return view('Blessing\TextureDesc::config', ['form' => $form]);
    }
}
