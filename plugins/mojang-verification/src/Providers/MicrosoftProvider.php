<?php

namespace GPlane\Mojang\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Log;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class MicrosoftProvider extends AbstractProvider
{
    protected $scopes = ['XboxLive.signin'];

    protected string $xbl_token;
    protected string $user_hash;

    protected string $xsts_token;

    protected string $minecraft_access_token;

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://login.live.com/oauth20_authorize.srf', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://login.live.com/oauth20_token.srf';
    }

    /**
     * @see https://wiki.vg/Microsoft_Authentication_Scheme
     */
    protected function getUserByToken($token)
    {
        $user = auth()->user();

        // Authenticate with XBox Live
        $response = Http::post('https://user.auth.xboxlive.com/user/authenticate', [
            'Properties' => [
                'AuthMethod' => 'RPS',
                'SiteName' => 'user.auth.xboxlive.com',
                'RpsTicket' => 'd='.$token,
            ],
            'RelyingParty' => 'http://auth.xboxlive.com',
            'TokenType' => 'JWT',
        ])->json();

        $xbl_token = $response['Token'];
        $user_hash = $response['DisplayClaims']['xui'][0]['uhs'];

        // Authenticate with XSTS (Xbox One Security Token Service)
        $response = Http::post('https://xsts.auth.xboxlive.com/xsts/authorize', [
            'Properties' => [
                'SandboxId' => 'RETAIL',
                'UserTokens' => [$xbl_token],
            ],
            'RelyingParty' => 'rp://api.minecraftservices.com/',
            'TokenType' => 'JWT',
        ])->json();

        if (Arr::exists($response, 'XErr')) {
            // TODO show detail error to user
            Log::channel('mojang-verification')->info("User [$user->email] authenticate with XSTS failed.", compact('response'));
            abort(500, trans('GPlane\Mojang::bind.failed.other'));
        }

        $xsts_token = $response['Token'];

        // Authenticate with Minecraft
        $response = Http::post('https://api.minecraftservices.com/authentication/login_with_xbox', [
            'identityToken' => 'XBL3.0 x='.$user_hash.';'.$xsts_token,
        ])->json();

        if (Arr::exists($response, 'error')) {
            // UNAUTHORIZED
            Log::channel('mojang-verification')->info("User [$user->email] authenticate with Minecraft failed.", compact('response'));
            abort(500);
        }

        $minecraft_access_token = $response['access_token'];

        // Get the profile
        $response = Http::withToken($minecraft_access_token)->get('https://api.minecraftservices.com/minecraft/profile')->json();

        if (Arr::exists($response, 'error')) {
            // logger($response);
            // NOT_FOUND
            // CONSTRAINT_VIOLATION
            Log::channel('mojang-verification')->info("User [$user->email] get the profile failed.", compact('response'));
            abort(403, trans('GPlane\Mojang::bind.failed.not-purchased'));
        }

        return $response;
    }

    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['name'],
        ]);
    }

    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}
