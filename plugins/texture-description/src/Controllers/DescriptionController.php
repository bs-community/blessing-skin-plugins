<?php

namespace Blessing\TextureDesc;

use App\Http\Controllers\Controller;
use App\Models\Texture;
use App\Models\User;
use Illuminate\Http\Request;
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
                        abort($statusCode, trans('skinlib.show.private'));
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
                ['required', 'string'],
                $limit > 0 ? ['max:'.$limit] : []
            ),
        ]);

        Description::updateOrCreate(['tid' => $texture->tid], ['desc' => $content]);

        $converter = new GithubFlavoredMarkdownConverter();

        return $converter->convertToHtml($content);
    }
}
