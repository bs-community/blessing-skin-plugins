<?php

namespace LittleSkin\YggdrasilConnect\Controllers;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\CryptoException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\AuthCode;
use Laravel\Passport\Client;
use LittleSkin\YggdrasilConnect\Exceptions\OAuth\AccessDeniedException;
use LittleSkin\YggdrasilConnect\Exceptions\OAuth\InvalidRequestException;
use LittleSkin\YggdrasilConnect\Exceptions\OAuth\OAuthException;
use LittleSkin\YggdrasilConnect\Models\Profile;
use LittleSkin\YggdrasilConnect\Models\User;
use LittleSkin\YggdrasilConnect\Models\UUID;
use LittleSkin\YggdrasilConnect\Scope;

class OIDCController extends Controller
{
    public function passportCallback(Request $request): RedirectResponse|View
    {
        $validation = Validator::make($request->all(), [
            'code' => ['required_without:error', 'string'],
            'state' => ['required', 'string'],
            'error' => ['required_without:code', 'string'],
            'error_description' => ['required_with:error', 'string'],
        ]);

        if ($validation->fails()) {
            abort(Response::HTTP_FORBIDDEN, trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.callback-request-invalid'));
        }

        $code = $request->input('code');
        $state = $request->input('state');

        if ($request->has('error')) {
            return static::handleRedirect($request->input('state'), null, $request->getQueryString());
        }

        try {
            $codeDecrypted = json_decode(Crypto::decryptWithPassword($code, Crypt::getKey()));

            $authCode = AuthCode::where(['id' => $codeDecrypted->auth_code_id, 'revoked' => false])->first();
            $user = auth()->user();
            if (empty($authCode) || $authCode->user_id != $user->uid || $authCode->expires_at->isPast() || $authCode->revoked) {
                throw new InvalidRequestException(trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.authorization-code-invalid'));
            }

            $client = Client::where('id', $authCode->client_id)->first();
            if (empty($client)) {
                throw new InvalidRequestException(trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.authorization-code-invalid'));
            }

            $codeId = $authCode->id;
            $scopes = json_decode($authCode->scopes);
            if (in_array(Scope::PROFILE_SELECT, $scopes)) {
                return view('LittleSkin\YggdrasilConnect::select-profile', [
                    'name' => $client->name,
                    'code_id' => $codeId,
                    'state' => $state,
                    'availableProfiles' => Profile::getAvailableProfiles($user),
                ]);
            }

            return static::handleRedirect($state, $codeId, null);
        } catch (CryptoException|InvalidRequestException $e) {
            if ($e instanceof OAuthException) {
                return static::handleRedirect($state, null, http_build_query($e->toArray()));
            }
            $exception = new InvalidRequestException(trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.authorization-code-invalid'));

            return static::handleRedirect($state, null, http_build_query($exception->toArray()));
        }
    }

    public function selectProfile(Request $request): RedirectResponse
    {
        $validation = Validator::make($request->all(), [
            'code_id' => ['required', 'string'],
            'state' => ['required', 'string'],
            'selectedProfile' => ['required', 'string'],
        ]);

        if ($validation->fails()) {
            abort(Response::HTTP_FORBIDDEN, trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.callback-request-invalid'));
        }

        try {
            $codeId = $request->input('code_id');
            $state = $request->input('state');
            $selectedProfile = $request->input('selectedProfile');

            if (DB::table('code_id_to_uuid')->where('code_id', $codeId)->exists()) {
                throw new InvalidRequestException(trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.authorization-code-invalid'));
            }

            $authCode = AuthCode::where(['id' => $codeId, 'revoked' => false])->first();
            $user = auth()->user();
            if (empty($authCode) || $authCode->user_id != $user->uid || $authCode->expires_at->isPast() || $authCode->revoked) {
                throw new InvalidRequestException(trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.authorization-code-invalid'));
            }

            $uuid = UUID::where('uuid', $selectedProfile)->first();
            if (empty($uuid) || $uuid->player->uid != $user->uid) {
                throw new InvalidRequestException(trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.authorization-code-invalid'));
            }

            DB::table('code_id_to_uuid')->insert([
                'code_id' => $codeId,
                'uuid' => $uuid->uuid,
            ]);

            return static::handleRedirect($state, $codeId, null);
        } catch (InvalidRequestException $e) {
            return static::handleRedirect($request->input('state'), null, http_build_query($e->toArray()));
        }
    }

    public function cancel(Request $request): RedirectResponse
    {
        $validation = Validator::make($request->all(), [
            'code_id' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        if ($validation->fails()) {
            abort(Response::HTTP_FORBIDDEN, trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.callback-request-invalid'));
        }

        $authCode = AuthCode::where('id', $request->input('code_id'))->first();
        $authCode->revoked = true;
        $authCode->save();

        $state = $request->input('state');

        $exception = new AccessDeniedException(trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.access-denied'));

        return static::handleRedirect($state, null, http_build_query($exception->toArray()));
    }

    private static function handleRedirect(string $state, ?string $code = null, ?string $errorQuery = null): RedirectResponse
    {
        $issuer = option('ygg_connect_server_url');
        $callbackUrl = "$issuer/interaction/$state/callback";

        if ($errorQuery) {
            return redirect()->away("$callbackUrl?$errorQuery");
        }

        return redirect()->away("$callbackUrl?".http_build_query(['code' => $code, 'state' => $state]));
    }

    public function getUserInfo()
    {
        /** @var User */
        $user = auth()->user();

        $resp = [
            'sub' => strval($user->uid),
        ];

        if ($user->tokenCan(Scope::PROFILE)) {
            $resp['nickname'] = $user->nickname;
            $resp['picture'] = url('avatar/user', $user->uid);
        }

        if ($user->tokenCan(Scope::EMAIL)) {
            $resp['email'] = $user->email;
            $resp['email_verified'] = true;
        }

        if ($user->tokenCan(Scope::PROFILE_SELECT)) {
            $profile = Profile::createFromUuid($user->yggdrasilToken()->selectedProfile);
            $resp['selectedProfile'] = [
                'id' => $profile->uuid,
                'name' => $profile->name,
            ];
        }

        if ($user->tokenCan(Scope::PROFILE_READ)) {
            $resp['availableProfiles'] = Profile::getAvailableProfiles($user);
        }

        return response()->json($resp);
    }

    /* Not good, not good
    public function userCode(Request $request): View
    {
        return view('LittleSkin\YggdrasilConnect::user-code',[
            'userCode' => $request->input('user_code'),
            'janusRoot' => option('ygg_connect_server_url'),
        ]);
    }
    */
}
