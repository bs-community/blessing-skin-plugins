<?php

namespace Blessing\RestrictedEmailDomains;

use App\Services\Hook;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ConfigController extends Controller
{
    public function render()
    {
        Hook::addScriptFileToPage(plugin('restricted-email-domains')->assets('config.js'));

        return view('Blessing\RestrictedEmailDomains::config');
    }

    public function list()
    {
        $allowList = json_decode(option('restricted-email-domains.allow', '[]'), true);
        $denyList = json_decode(option('restricted-email-domains.deny', '[]'), true);

        return [
            'allow' => array_values($allowList),
            'deny' => array_values($denyList),
        ];
    }

    public function save(Request $request, $list)
    {
        $json = json_encode(array_values($request->input()));
        option(["restricted-email-domains.$list" => $json]);

        return response()->noContent();
    }
}
