<?php

namespace Yggdrasil\Controllers;

use DB;
use Log;
use Cache;
use App\Models\Player;
use Yggdrasil\Utils\UUID;
use Illuminate\Http\Request;
use Yggdrasil\Models\Profile;
use Illuminate\Routing\Controller;
use Yggdrasil\Exceptions\ForbiddenOperationException;
use Yggdrasil\Services\YggdrasilServiceInterface as Yggdrasil;

class ServerController extends Controller
{
    public function joinServer(Request $request)
    {
        $server = $request->get('serverId');
        $accessToken = UUID::format($request->get('accessToken'));
        $uuid = UUID::format($request->get('selectedProfile'));

        $result = DB::table('uuid')->where('uuid', $uuid)->first();

        Log::info("Try to join server", [$request->json()->all()]);

        if (! $result) {
            throw new ForbiddenOperationException('Invalid uuid');
        }

        $player = Player::where('player_name', $result->name)->first();

        if (! $player) {
            throw new ForbiddenOperationException('Invalid uuid');
        }

        $identification = $player->user()->first()->email;

        if ($cache = Cache::get("I$identification")) {

            $token = unserialize($cache);

            if ($token->getAccessToken() != $accessToken) {
                throw new ForbiddenOperationException('Invalid access token');
            }

            Cache::forever("S$server", $uuid);

            Log::info("Player [$uuid] joined the server [$server]");

            return response('')->setStatusCode(204);
        } else {
            throw new ForbiddenOperationException('Invalid uuid');
        }
    }

    public function hasJoinedServer(Request $request)
    {
        $server = $request->get('serverId');
        $username = $request->get('username');

        Log::info("Check if player [$username] has joined the server [$server]", [$_GET]);

        if ($uuid = Cache::get("S$server")) {
            $profile = Profile::createFromUuid($uuid);

            if ($username == $profile->getName()) {
                Cache::forget("S$server");

                return response()->json()->setContent($profile);
            }
        }
    }
}
