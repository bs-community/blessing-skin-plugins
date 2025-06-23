<?php

namespace LittleSkin\YggdrasilConnect\Controllers;

use App\Models\Player;
use Blessing\Filter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\ForbiddenOperationException;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;
use LittleSkin\YggdrasilConnect\Models\AccessToken;
use LittleSkin\YggdrasilConnect\Models\Profile;
use LittleSkin\YggdrasilConnect\Models\UUID;
use LittleSkin\YggdrasilConnect\Models\User;
use Vectorface\Whip\Whip;

class SessionController extends Controller
{
    public function joinServer(Request $request, Filter $filter): Response
    {
        $validation = Validator::make($request->all(), [
            'selectedProfile' => ['required', 'string'],
            'serverId' => ['required', 'string'],
        ]);

        if($validation->fails()) {
            throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::exceptions.illegal'));
        }

        /** @var User */
        $user = auth()->user();
        /** @var AccessToken */
        $token = $user->yggdrasilToken();
        $selectedProfile = $request->input('selectedProfile');
        $serverId = $request->input('serverId');

        $whip = new Whip();
        $ip = $whip->getValidIpAddress();
        $ip = $filter->apply('client_ip', $ip);

        $session = ['profile' => $selectedProfile, 'ip' => $ip];

        Log::channel('ygg')->info("Player [$selectedProfile] is trying to join server [$serverId] from [$ip] with access token [$token->jwt]");

        if ($token->selectedProfile !== $selectedProfile) {
            Log::channel('ygg')->info("Player [$selectedProfile] does not match the selected profile in Access Token [{$token->selectedProfile}]");
            throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.player.not-match'));
        }

        // 中间件里 canJoinServer() 的时候已经确定过角色存在了
        $result = UUID::where('uuid', $selectedProfile)->first();
        /** @var Player */
        $player = $result->player;

        if ($token->owner->uid != $result->player->uid) {
            Log::channel('ygg')->info("Player [$selectedProfile] does not belong to user [{$token->owner->uid}]");
            throw new ForbiddenOperationException(trans("LittleSkin\\YggdrasilConnect::exceptions.player.owner"));
        }

        Log::channel('ygg')->info("Player [$selectedProfile]'s name is [$player->name], belongs to user [$player->uid]");

        // 加入服务器
        Cache::put("yggdrasil-server-$serverId", $session, 120);

        Log::channel('ygg')->info("Player [$selectedProfile] successfully joined the server [$serverId]");

        ygg_log([
            'action' => 'join',
            'user_id' => $player->uid,
            'player_id' => $player->pid,
            'parameters' => json_encode($request->except('accessToken')),
        ]);

        return response()->noContent();
    }

    public function hasJoinedServer(Request $request) : Response | JsonResponse
    {

        $validation = Validator::make($request->all(), [
            'username' => ['required', 'string'],
            'serverId' => ['required', 'string'],
            'ip' => ['nullable', 'ip'],
        ]);

        if($validation->fails()) {
            return response()->noContent();
        }

        $name = $request->input('username');
        $serverId = $request->input('serverId');
        $ip = $request->input('ip');

        Log::channel('ygg')->info("Checking if player [$name] has joined the server [$serverId] with IP [$ip]");

        // 检查是否进行过 join 请求
        $session = Cache::get("yggdrasil-server-$serverId");
        if ($session) {
            $profile = Profile::createFromUuid($session['profile']);

            if ($ip && $ip !== $session['ip']) {
                return response()->noContent();
            }

            if ($name === $profile->name) {
                Log::channel('ygg')->info("Player [$name] was in the server [$serverId]");

                // 这里返回的 Profile 必须带材质的数据签名
                $response = $profile->serialize(false);
                Log::channel('ygg')->info("Returning player [$name]'s profile", [$response]);

                ygg_log(array_merge([
                    'action' => 'has_joined',
                    'user_id' => $profile->player->uid,
                    'player_id' => $profile->player->pid,
                    'parameters' => json_encode($request->except('username')),
                ], $ip ? compact('ip') : []));

                return response()->json()->setContent($response);
            }
        }

        Log::channel('ygg')->info("Player [$name] was not in the server [$serverId]");

        return response()->noContent();
    }

}
