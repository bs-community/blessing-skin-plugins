<?php

namespace GPlane\Mojang;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Mail extends Mailable
{
    use Queueable, SerializesModels;

    public $nickname;
    public $playerName;

    public function __construct($nickname, $playerName)
    {
        $this->nickname = $nickname;
        $this->playerName = $playerName;
    }

    public function build()
    {
        return $this->from(config('mail.from'))
            ->subject('角色属主更改通知')
            ->view('GPlane\\Mojang::mail');
    }
}
