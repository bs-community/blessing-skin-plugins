<?php

namespace GPlane\ShareRegistrationLink;

use App\Models\User;
use Illuminate\Http\Request;

class CheckCode
{
    /** @var Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(User $user)
    {
        $code = $this->request->input('share');
        $record = Record::where('code', $code)->first();
        if ($record) {
            $sharer = User::find($record->sharer);
            if ($sharer) {
                $sharer->score += option('reg_link_sharer_score', 50);
                $sharer->save();
            }

            $user->score += option('reg_link_sharee_score', 0);
            $user->save();
        }
    }
}
