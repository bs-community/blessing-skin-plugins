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

        return ['allow' => $allowList, 'deny' => $denyList];
    }

    public function save(Request $request, $list)
    {
        option([
            "restricted-email-domains.$list" => json_encode($request->input()),
        ]);

        return response()->noContent();
    }
}
