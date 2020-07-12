<?php

namespace GPlane\Mojang;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Mail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /** @var User */
    public $user;
    public $playerName;

    public function __construct(User $user, $playerName)
    {
        $this->user = $user;
        $this->playerName = $playerName;
    }

    public function build()
    {
        return $this->from(config('mail.from'))
            ->subject(trans('GPlane\Mojang::bind.notification.title', [], $this->user->locale))
            ->view('GPlane\\Mojang::mail', [
                'nickname' => $this->user->nickname,
                'player' => $this->playerName,
                'score' => option('score_per_player'),
                'site_name' => option('site_name_'.$this->user->locale),
            ]);
    }
}
