<?php

namespace Yggdrasil\Controllers;

use Log;
use App\Models\Player;
use Yggdrasil\Utils\UUID;
use Illuminate\Http\Request;
use Yggdrasil\Models\Profile;
use Illuminate\Routing\Controller;
use Yggdrasil\Exceptions\NotFoundException;
use Yggdrasil\Services\YggdrasilServiceInterface as Yggdrasil;

class ProfileController extends Controller
{
    public function getProfileFromName($username)
    {
        $player = Player::where('player_name', $username)->first();

        if (! $player) {
            throw new NotFoundException('Player not found');
        }

        $profile = Profile::createFromPlayer($player);

        return response()->json()->setContent($profile);
    }

    public function getProfileFromUuid($uuid)
    {
        $formattedUuid = UUID::format($uuid);

        $profile = Profile::createFromUuid($formattedUuid);

        return response()->json()->setContent($profile);
    }

    public function searchProfile(Request $request)
    {
        $profiles = [];

        foreach ($request->json() as $name) {
            $player = Player::where('player_name', $name)->first();

            if ($player) {
                $profile = Profile::createFromPlayer($player);

                $profiles[] = [
                    'id' => $profile->getUuid(),
                    'name' => $name
                ];
            }
        }

        return json($profiles);
    }
}
