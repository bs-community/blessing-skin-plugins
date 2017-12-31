<?php

namespace Yggdrasil\Controllers;

use DB;
use Cache;
use App\Models\Player;
use Yggdrasil\Utils\Log;
use Yggdrasil\Utils\UUID;
use Illuminate\Http\Request;
use Yggdrasil\Models\Profile;
use Illuminate\Routing\Controller;
use Yggdrasil\Exceptions\ForbiddenOperationException;
use Yggdrasil\Service\YggdrasilServiceInterface as Yggdrasil;

class SessionController extends Controller
{
    public function joinServer(Request $request, Yggdrasil $ygg)
    {
        $accessToken = UUID::format($request->get('accessToken'));
        $selectedProfile = UUID::format($request->get('selectedProfile'));
        $serverId = $request->get('serverId');

        Log::info("Try to join server", [$request->json()->all()]);

        $ygg->joinServer($accessToken, $selectedProfile, $serverId);

        Log::info("Player [$selectedProfile] joined the server [$serverId]");

        return response('')->setStatusCode(204);
    }

    public function hasJoinedServer(Request $request, Yggdrasil $ygg)
    {
        $name = $request->get('username');
        $serverId = $request->get('serverId');
        $ip = $request->get('ip');

        Log::info("Check if player [$name] has joined the server [$serverId]", [$_GET]);

        $profile = $ygg->hasJoinedServer($name, $serverId, $ip);

        if ($profile) {
            return response()->json()->setContent($profile);
        } else {
            return response('')->setStatusCode(204);
        }
    }
}
