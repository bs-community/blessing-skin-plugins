<?php

namespace Yggdrasil\Controllers;

use Cache;
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
        $accessToken = $request->get('accessToken');
        $uuid = UUID::format($request->get('selectedProfile'));

        if ($cache = Cache::get("U$uuid")) {
            $token = unserialize($cache);

            if ($token->getAccessToken() != $accessToken) {
                throw new ForbiddenOperationException('Invalid access token');
            }

            Cache::forever("S$server", serialize($token));

            return response('')->setStatusCode(204);
        } else {
            throw new ForbiddenOperationException('Invalid uuid');
        }
    }

    public function hasJoinedServer(Request $request)
    {
        $server = $request->get('serverId');
        $username = $request->get('username');

        if ($cache = Cache::get("S$server")) {
            $token = unserialize($cache);
            $profile = Profile::createFromUuid($token->getOwnerUuid());

            if ($username == $profile->getName()) {
                Cache::forget("S$server");

                return response()->json()->setContent($profile);
            }
        }
    }
}
