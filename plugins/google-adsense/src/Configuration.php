<?php

namespace BigCake\googleadsense;

use App\Services\OptionForm;
use Option;

class Configuration
{
    public function render()
    {
        $form = Option::form('Google Adsense', 'client_id', function (OptionForm $form) {
            $form->text('client_id', trans('BigCake\googleadsense::config.client_id'));
        })->handle();

        return view('BigCake\googleadsense::config', ['form' => $form]);
    }
}
