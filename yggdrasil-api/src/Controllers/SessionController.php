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
use Yggdrasil\Exceptions\IllegalArgumentException;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class SessionController extends Controller
{
    public function joinServer(Request $request)
    {
        $accessToken = UUID::format($request->get('accessToken'));
        $selectedProfile = UUID::format($request->get('selectedProfile'));
        $serverId = $request->get('serverId');

        Log::info("Player [$selectedProfile] is trying to join server [$serverId] with token [$accessToken]");

        $result = DB::table('uuid')->where('uuid', $selectedProfile)->first();

        if (! $result) {
            throw new IllegalArgumentException("无效的 Profile UUID [$selectedProfile]，它不属于任何角色");
        }

        $player = Player::where('player_name', $result->name)->first();

        if (! $player) {
            throw new IllegalArgumentException("指定的角色 [$result->name] 不存在");
        }

        $identification = strtolower($player->user()->first()->email);

        Log::info("Player name is [$player->player_name], belongs to user [$identification]");

        if ($cache = Cache::get("ID_$identification")) {

            $token = unserialize($cache);

            Log::info("All access tokens issued for user [$identification] are as listed", [$token]);

            if ($token->accessToken != $accessToken) {
                throw new IllegalArgumentException('无效的 AccessToken，请重新登录');
            }

            // 加入服务器
            Cache::forever("SERVER_$serverId", $selectedProfile);
        } else {

            Log::info("No access token issued for user [$identification]", [$cache]);

            // 指定角色所属的用户没有签发任何令牌
            throw new IllegalArgumentException('未查找到有效的登录信息，请重新登录');
        }

        Log::info("Player [$selectedProfile] successfully joined the server [$serverId]");

        return response('')->setStatusCode(204);
    }

    public function hasJoinedServer(Request $request)
    {
        $name = $request->get('username');
        $serverId = $request->get('serverId');
        $ip = $request->get('ip');

        Log::info("Checking if player [$name] has joined the server [$serverId] with IP [$ip]");

        // 检查是否进行过 join 请求
        if ($selectedProfile = Cache::get("SERVER_$serverId")) {
            $profile = Profile::createFromUuid($selectedProfile);

            // TODO: 检查 IP 地址
            if ($name === $profile->name) {
                // 检查完成后马上删除缓存键值对
                Cache::forget("SERVER_$serverId");

                // 这里返回的 Profile 必须带材质的数据签名
                $response = $profile->serialize(false);

                Log::info("Player [$name] was in the server [$serverId], returning his profile", [$response]);
                return response()->json()->setContent($response);
            }
        }

        Log::info("Player [$name] was not in the server [$serverId]");
        return response('')->setStatusCode(204);
    }
}
