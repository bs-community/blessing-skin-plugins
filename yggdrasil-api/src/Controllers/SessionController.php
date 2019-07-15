<?php

namespace Yggdrasil\Controllers;

use DB;
use Log;
use Cache;
use Schema;
use App\Models\User;
use App\Models\Player;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Models\Token;
use Illuminate\Http\Request;
use Yggdrasil\Models\Profile;
use Illuminate\Routing\Controller;
use Yggdrasil\Exceptions\IllegalArgumentException;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class SessionController extends Controller
{
    public function joinServer(Request $request)
    {
        $accessToken = $request->get('accessToken');
        $selectedProfile = $request->get('selectedProfile');
        $serverId = $request->get('serverId');

        Log::channel('ygg')->info("Player [$selectedProfile] is trying to join server [$serverId] with access token [$accessToken]");

        $result = DB::table('uuid')->where('uuid', $selectedProfile)->first();

        if (! $result) {
            // 据说 Mojang 在这种情况下是会返回 403 的，我就照做好了
            throw new ForbiddenOperationException("无效的 Profile UUID [$selectedProfile]，它不属于任何角色");
        }

        $player = Player::where('name', $result->name)->first();

        if (! $player) {
            // 删除已失效的 UUID 映射（e.g. 其对应的角色已被删除）
            DB::table('uuid')->where('uuid', $selectedProfile)->delete();

            throw new ForbiddenOperationException("无效的 Profile UUID [$selectedProfile]，它不属于任何角色");
        }

        $identification = strtolower($player->user->email);

        Log::channel('ygg')->info("Player [$selectedProfile]'s name is [$player->name], belongs to user [$identification]");

        $token = Token::lookup($accessToken);
        if ($token && $token->isValid()) {

            Log::channel('ygg')->info("All access tokens issued for user [$identification] are as listed", [$token]);

            if ($token->accessToken != $accessToken) {
                throw new ForbiddenOperationException('无效的 AccessToken，请重新登录');
            }

            if ($token->profileId != $selectedProfile) {
                throw new ForbiddenOperationException('请求的角色与令牌绑定的角色不一致');
            }

            if ($player->user->permission == User::BANNED) {
                throw new ForbiddenOperationException('你已经被本站封禁，详情请询问管理人员');
            }

            // 加入服务器
            Cache::forever("SERVER_$serverId", $selectedProfile);
        } elseif ($this->mojangVerified($player) && $this->validateMojang($accessToken)) {
            if ($player->user->permission == User::BANNED) {
                throw new ForbiddenOperationException('你已经被本站封禁，详情请询问管理人员');
            }

            Log::channel('ygg')->info("Player [$player->name] is joining server with Mojang verified account.");
            // 加入服务器
            Cache::forever("SERVER_$serverId", $selectedProfile);
        } else {
            // 指定角色所属的用户没有签发任何令牌
            throw new ForbiddenOperationException('未查找到有效的登录信息，请重新登录');
        }

        Log::channel('ygg')->info("Player [$selectedProfile] successfully joined the server [$serverId]");

        ygg_log([
            'action' => 'join',
            'user_id' => $player->uid,
            'player_id' => $player->pid,
            'parameters' => json_encode($request->except('accessToken'))
        ]);

        return response('')->setStatusCode(204);
    }

    public function hasJoinedServer(Request $request)
    {
        $name = $request->get('username');
        $serverId = $request->get('serverId');
        $ip = $request->get('ip');

        Log::channel('ygg')->info("Checking if player [$name] has joined the server [$serverId] with IP [$ip]");

        // 检查是否进行过 join 请求
        if ($selectedProfile = Cache::get("SERVER_$serverId")) {
            $profile = Profile::createFromUuid($selectedProfile);

            // TODO: 检查 IP 地址
            if ($name === $profile->name) {
                // 检查完成后马上删除缓存键值对
                Cache::forget("SERVER_$serverId");
                Log::channel('ygg')->info("Player [$name] was in the server [$serverId]");

                // 这里返回的 Profile 必须带材质的数据签名
                $response = $profile->serialize(false);
                Log::channel('ygg')->info("Returning player [$name]'s profile", [$response]);

                ygg_log(array_merge([
                    'action' => 'has_joined',
                    'user_id' => $profile->player->uid,
                    'player_id' => $profile->player->pid,
                    'parameters' => json_encode($request->except('username'))
                ], ($ip ? compact('ip') : [])));

                return response()->json()->setContent($response);
            }
        }

        Log::channel('ygg')->info("Player [$name] was not in the server [$serverId]");
        return response('')->setStatusCode(204);
    }

    protected function mojangVerified($player)
    {
        if (! Schema::hasTable('mojang_verifications')) {
            return false;
        }

        return DB::table('mojang_verifications')->where('user_id', $player->uid)->exists();
    }

    protected function validateMojang($accessToken)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', 'https://authserver.mojang.com/validate', [
                'json' => ['accessToken' => $accessToken]
            ]);
            return $response->getStatusCode() === 204;
        } catch (\Exception $e) {
            return false;
        }
    }
}
