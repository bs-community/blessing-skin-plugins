<?php

namespace Yggdrasil\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Log;
use Yggdrasil\Exceptions\ForbiddenOperationException;
use Yggdrasil\Models\Profile;

class ProfileController extends Controller
{
    public function getProfileFromUuid($uuid)
    {
        $profile = Profile::createFromUuid($uuid);

        Log::channel('ygg')->info("Try to get profile of player with uuid [$uuid]");

        if ($profile) {
            Log::channel('ygg')->info("Returning profile for uuid [$uuid]", [$profile->serialize()]);

            return response()->json()->setContent($profile);
        } else {
            // UUID 不存在就返回 204
            Log::channel('ygg')->info("Profile not found for uuid [$uuid]");

            return response()->noContent();
        }
    }

    public function getProfileFromName($name)
    {
        $player = Player::where('name', $name)->first();

        if (empty($player)) {
            return response()->noContent();
        }

        $profile = Profile::createFromPlayer($player);

        return response()->json()->setContent($profile);
    }

    public function searchMultipleProfiles(Request $request)
    {
        $names = array_unique($request->json()->all());

        Log::channel('ygg')->info('Search profiles by player names as listed', array_values($names));

        if (count($names) > option('ygg_search_profile_max')) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.player.query-max', ['count' => option('ygg_search_profile_max')]));
        }

        $profiles = [];

        foreach ($names as $name) {
            $player = Player::where('name', $name)->first();

            if ($player) {
                $profile = Profile::createFromPlayer($player);

                $profiles[] = [
                    'id' => $profile->uuid,
                    'name' => $name,
                ];
            }
        }

        return json($profiles);
    }

    public function searchSingleProfile($username)
    {
        $player = Player::where('name', $username)->first();
        if (empty($player)) {
            return response()->noContent();
        }

        $profile = Profile::createFromPlayer($player);

        return [
            'id' => $profile->uuid,
            'name' => $username,
        ];
    }
}
