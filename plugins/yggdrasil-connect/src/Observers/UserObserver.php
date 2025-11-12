<?php

namespace LittleSkin\YggdrasilConnect\Observers;

use App\Models\User;
use App\Services\Facades\Option;
use Laravel\Passport\Token;

class UserObserver {
    public function updated(User $user) {
        // Invalidate tokens when the user's password is changed
        if($user->isDirty('password')) {
            Token::where([
                ['user_id', '=', $user->uid],
                ['client_id', '=', intval(env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'))],
                ['revoked', '=', false],
                ['created_at', '>', now()->subSeconds(Option::get('ygg_token_expire_2'))],
            ])->get()->each(function (Token $token) {
                $token->revoke();
            });
        }
    }
}