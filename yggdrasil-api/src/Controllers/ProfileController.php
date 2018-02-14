<?php

namespace Yggdrasil\Controllers;

use App\Models\Player;
use Yggdrasil\Utils\Log;
use Yggdrasil\Utils\UUID;
use Illuminate\Http\Request;
use Yggdrasil\Models\Profile;
use Illuminate\Routing\Controller;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class ProfileController extends Controller
{
    public function getProfileFromUuid($uuid)
    {
        $profile = Profile::createFromUuid(UUID::format($uuid));

        if ($profile) {
            Log::info("Returning profile for uuid [$uuid]", [$profile->serialize()]);
            return response()->json()->setContent($profile);
        } else {
            // UUID 不存在就返回 204
            Log::info("Profile not found for uuid [$uuid]");
            return response('')->setStatusCode(204);
        }
    }

    public function getProfileFromName($name)
    {
        $player = Player::where('player_name', $name)->first();

        if (! $player) {
            // 角色不存在
            return response('')->setStatusCode(204);
        }

        $profile = Profile::createFromPlayer($player);

        return response()->json()->setContent($profile);
    }

    public function searchProfile(Request $request)
    {
        $profiles = [];

        if (count($request->json()) > option('ygg_search_profile_max')) {
            throw new ForbiddenOperationException(sprintf('一次最多只能查询 %s 个角色哦', option('ygg_search_profile_max')));
        }

        foreach ($request->json() as $name) {
            $player = Player::where('player_name', $name)->first();

            if ($player) {
                $profile = Profile::createFromPlayer($player);

                $profiles[] = [
                    'id' => $profile->uuid,
                    'name' => $name
                ];
            }
        }

        return json($profiles);
    }
}
