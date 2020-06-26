<?php

namespace Blessing\Gtag;

use App\Services\OptionForm;
use Option;

class Configuration
{
    public function render()
    {
        $form = Option::form('gtag', 'gtag.js', function (OptionForm $form) {
            $form->text('ga_id', trans('Blessing\Gtag::config.ga_id'));
        })->handle();

        return view('Blessing\Gtag::config', ['form' => $form]);
    }
}
