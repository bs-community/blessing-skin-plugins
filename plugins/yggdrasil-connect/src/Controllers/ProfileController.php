<?php

namespace LittleSkin\YggdrasilConnect\Controllers;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use Blessing\Filter;
use Blessing\Rejection;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\ForbiddenOperationException;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\NotFoundException;
use LittleSkin\YggdrasilConnect\Models\Profile;
use LittleSkin\YggdrasilConnect\Utils\Http;

class ProfileController extends Controller
{
    public function getProfileFromUuid($uuid): Response | JsonResponse
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

    public function getProfileFromName($name): Response | JsonResponse
    {
        $player = Player::where('name', $name)->first();

        if (empty($player)) {
            return response()->noContent();
        }

        $profile = Profile::createFromPlayer($player);

        return response()->json()->setContent($profile);
    }

    public function searchMultipleProfiles(Request $request): JsonResponse
    {
        $names = array_unique($request->json()->all());

        Log::channel('ygg')->info('Search profiles by player names as listed', array_values($names));

        if (count($names) > option('ygg_search_profile_max')) {
            throw new ForbiddenOperationException(trans('LittleSkin\YggdrasilConnect::exceptions.player.query-max', ['count' => option('ygg_search_profile_max')]));
        }

        $profiles = [];

        foreach ($names as $name) {
            $player = Player::where('name', $name)->first();

            if ($player) {
                $profile = Profile::createFromPlayer($player);

                $profiles[] = [
                    'id' => $profile->uuid,
                    'name' => $profile->player->name,
                ];
            }
        }

        return json($profiles);
    }

    public function searchSingleProfile($username): Response | JsonResponse
    {
        $player = Player::where('name', $username)->first();
        if (empty($player)) {
            return response()->noContent();
        }

        $profile = Profile::createFromPlayer($player);

        return json([
            'id' => $profile->uuid,
            'name' => $profile->player->name,
        ]);
    }

    public function uploadTexture(Request $request, Dispatcher $dispatcher, Filter $filter, $uuid, $type): Response
    {
        $data = Http::parseRequest();

        $profile = Profile::createFromUuid($uuid);
        if (empty($profile)) {
            throw new NotFoundException(trans('LittleSkin\YggdrasilConnect::exceptions.player.not-exist'));
        }

        $file = new UploadedFile($data['file']['tmp_name'], true);
        $file = $filter->apply('uploaded_texture_file', $file);

        $name = Str::replaceLast('.png', '', $data['file']['name']);
        $name = $filter->apply('uploaded_texture_name', $name, [$file]);

        $can = $filter->apply('can_upload_texture', true, [$file, $name]);
        if ($can instanceof Rejection) {
            throw new ForbiddenOperationException($can->getReason());
        }

        $isAlex = Arr::get($data, 'model') === 'slim';
        $size = getimagesize($file);
        $ratio = $size[0] / $size[1];
        if ($type === 'cape') {
            if ($ratio !== 2) {
                $message = trans('skinlib.upload.invalid-size', [
                    'type' => trans('general.cape'),
                    'width' => $size[0],
                    'height' => $size[1],
                ]);

                throw new IllegalArgumentException($message);
            }
        } elseif ($type === 'skin') {
            if ($ratio !== 2 && $ratio !== 1 || $isAlex && $ratio === 2) {
                $message = trans('skinlib.upload.invalid-size', [
                    'type' => trans('general.skin'),
                    'width' => $size[0],
                    'height' => $size[1],
                ]);

                throw new IllegalArgumentException($message);
            }
            if ($size[0] % 64 !== 0 || $size[1] % 32 !== 0) {
                $message = trans('skinlib.upload.invalid-hd-skin', [
                    'type' => trans('general.skin'),
                    'width' => $size[0],
                    'height' => $size[1],
                ]);

                throw new IllegalArgumentException($message);
            }
        }

        $hash = hash_file('sha256', $file);
        $hash = $filter->apply('uploaded_texture_hash', $hash, [$file]);

        /** @var User */
        $user = $profile->player->user;

        $duplicate = Texture::where('hash', $hash)->where('uploader', $user->uid)->first();
        if ($duplicate) {
            $texture = $duplicate;

            if ($user->closet->where('hash', $hash)->isEmpty()) {
                $cost = (int) option('score_per_closet_item');
                if ($cost > $user->score) {
                    throw new ForbiddenOperationException(trans('skinlib.upload.lack-score'));
                }

                $user->closet()->attach($texture->tid, ['item_name' => $name]);
                $user->save();
            }
        } else {
            $size = ceil($file->getSize() / 1024);
            $cost = (int) option('private_score_per_storage') * $size + (int) option('score_per_closet_item');
            if ($cost > $user->score) {
                throw new ForbiddenOperationException(trans('skinlib.upload.lack-score'));
            }

            $dispatcher->dispatch('texture.uploading', [$file, $name, $hash]);

            $texture = new Texture();
            $texture->name = $name;
            $texture->type = $type === 'cape' ? 'cape' : ($isAlex ? 'alex' : 'steve');
            $texture->hash = $hash;
            $texture->size = $size;
            $texture->public = false;
            $texture->uploader = $user->uid;
            $texture->likes = 1;
            $texture->save();

            /** @var FilesystemAdapter */
            $disk = Storage::disk('textures');
            if ($disk->missing($hash)) {
                $file->storePubliclyAs('', $hash, ['disk' => 'textures']);
            }

            $user->score -= $cost;
            $user->closet()->attach($texture->tid, ['item_name' => $name]);
            $user->save();

            $dispatcher->dispatch('texture.uploaded', [$texture, $file]);
        }

        $player = $profile->player;
        $can = $filter->apply('can_set_texture', true, [$player, $type, $texture->tid]);
        if ($can instanceof Rejection) {
            throw new ForbiddenOperationException($can->getReason(), 1);
        }

        $dispatcher->dispatch('player.texture.updating', [$player, $texture]);

        $player->update(["tid_$type" => $texture->tid]);

        $dispatcher->dispatch('player.texture.updated', [$player, $texture]);

        return response()->noContent();
    }

    public function resetTexture(Request $request, Dispatcher $dispatcher, Filter $filter, $uuid, $type): Response
    {
        $profile = Profile::createFromUuid($uuid);
        if (empty($profile)) {
            throw new NotFoundException(trans('LittleSkin\YggdrasilConnect::exceptions.player.not-exist'));
        }

        $player = $profile->player;
        $can = $filter->apply('can_clear_texture', true, [$player, $type]);
        if ($can instanceof Rejection) {
            throw new ForbiddenOperationException($can->getReason());
        }

        $dispatcher->dispatch('player.texture.resetting', [$player, $type]);

        $player->update(["tid_$type" => 0]);

        $dispatcher->dispatch('player.texture.reset', [$player, $type]);

        return response()->noContent();
    }
}
