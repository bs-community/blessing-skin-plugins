<?php

namespace Blessing\ConfigGenerator;

use App\Services\Hook;
use App\Services\PluginManager;
use Parsedown;

class Controller
{
    public function generate(PluginManager $plugins)
    {
        $siteName = option_localized('site_name');

        $mojang = ['name' => 'Mojang', 'type' => 'MojangAPI'];
        $self = [
            'name' => $siteName,
            'root' => url('/csl/'),
            'type' => 'CustomSkinAPI',
        ];
        if (option('csl_first', 'self') === 'self') {
            $csl = [$self, $mojang];
        } else {
            $csl = [$mojang, $self];
        }

        $usm = [
            'rootURIs' => [url('/csm/')],
            'legacySkinURIs' => [],
            'legacyCapeURIs' => [],
        ];

        Hook::addScriptFileToPage(plugin_assets('config-generator', 'generator.js'));

        $jsonConstants = JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES;

        $intro = option_localized('config_generator_intro', '');
        $intro = (new Parsedown())->text($intro);

        return view('Blessing\ConfigGenerator::generator', [
            'csl' => json_encode($csl, $jsonConstants),
            'has_usm' => optional($plugins->get('usm-api'))->isEnabled(),
            'usm' => json_encode($usm, $jsonConstants),
            'has_legacy' => optional($plugins->get('legacy-api'))->isEnabled(),
            'site' => $siteName,
            'custom_intro' => $intro,
        ]);
    }
}
