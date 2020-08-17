<?php

namespace Blessing\TextureDesc\Controllers;

use App\Models\Texture;
use App\Models\User;
use Blessing\TextureDesc\Models\Description;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class DescriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            /** @var User|null */
            $user = auth()->user();

            /** @var Texture */
            $texture = $request->route('texture');

            if (!$texture->public) {
                if (empty($user) || ($user->uid !== $texture->uploader && !$user->isAdmin())) {
                    $statusCode = (int) option('status_code_for_private');
                    if ($statusCode === 404) {
                        abort($statusCode, trans('skinlib.show.deleted'));
                    } else {
                        abort(403, trans('skinlib.show.private'));
                    }
                }
            }

            return $next($request);
        });
    }

    public function read(Texture $texture)
    {
        /** @var string */
        $description = Description::where('tid', $texture->tid)->value('desc') ?? '';

        $converter = new GithubFlavoredMarkdownConverter();

        return $converter->convertToHtml($description);
    }

    public function update(Request $request, Texture $texture)
    {
        /** @var User */
        $currentUser = auth()->user();

        if ($texture->uploader !== auth()->id() && !$currentUser->isAdmin()) {
            abort(403, trans('skinlib.no-permission'));
        }

        $limit = (int) option('textures_desc_limit', 0);
        ['content' => $content] = $request->validate([
            'content' => array_merge(
                ['nullable', 'string'],
                $limit > 0 ? ['max:'.$limit] : []
            ),
        ]);
        $content = $content ?: '';

        Description::updateOrCreate(['tid' => $texture->tid], ['desc' => $content]);

        $converter = new GithubFlavoredMarkdownConverter();

        return $converter->convertToHtml($content);
    }

    public function raw(Texture $texture)
    {
        $raw = Description::where('tid', $texture->tid)->value('desc') ?? '';

        return response($raw, 200, [
            'Content-Type' => 'text/markdown',
        ]);
    }
}
