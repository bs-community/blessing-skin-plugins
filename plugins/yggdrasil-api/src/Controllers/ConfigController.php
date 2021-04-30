<?php

namespace Yggdrasil\Controllers;

use App\Services\Hook;
use App\Services\OptionForm;
use App\Services\PluginManager;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Option;
use Yggdrasil\Exceptions\IllegalArgumentException;

class ConfigController extends Controller
{
    public function render()
    {
        $commonForm = Option::form('common', trans('Yggdrasil::config.common.title'), function (OptionForm $form) {
            $form->select('ygg_uuid_algorithm', trans('Yggdrasil::config.common.ygg_uuid_algorithm.title'))
                ->option('v3', trans('Yggdrasil::config.common.ygg_uuid_algorithm.v3'))
                ->option('v4', trans('Yggdrasil::config.common.ygg_uuid_algorithm.v4'))
                ->hint(trans('Yggdrasil::config.common.ygg_uuid_algorithm.hint'));
            $form->text('ygg_token_expire_1', trans('Yggdrasil::config.common.ygg_token_expire_1.title'));
            $form->text('ygg_token_expire_2', trans('Yggdrasil::config.common.ygg_token_expire_2.title'))
                ->description(trans('Yggdrasil::config.common.ygg_token_expire_2.description'));
            $form->text('ygg_tokens_limit', trans('Yggdrasil::config.common.ygg_tokens_limit.title'))
                ->description(trans('Yggdrasil::config.common.ygg_tokens_limit.description'));
            $form->text('ygg_rate_limit', trans('Yggdrasil::config.common.ygg_rate_limit.title'))
                ->hint(trans('Yggdrasil::config.common.ygg_rate_limit.hint'));
            $form->text('ygg_skin_domain', trans('Yggdrasil::config.common.ygg_skin_domain.title'))
                ->description(trans('Yggdrasil::config.common.ygg_skin_domain.description'));
            $form->text('ygg_search_profile_max', trans('Yggdrasil::config.common.ygg_search_profile_max.title'))
                ->hint(trans('Yggdrasil::config.common.ygg_search_profile_max.hint'));
            $form->checkbox('ygg_show_config_section', trans('Yggdrasil::config.common.ygg_show_config_section.title'))
                ->label(trans('Yggdrasil::config.common.ygg_show_config_section.label'));
            $form->checkbox('ygg_enable_ali', trans('Yggdrasil::config.common.ygg_enable_ali.title'))
                ->label(trans('Yggdrasil::config.common.ygg_enable_ali.label'));
        })->handle();

        $keypairForm = Option::form('keypair', trans('Yggdrasil::config.keypair.title'), function (OptionForm $form) {
            $form->textarea('ygg_private_key', trans('Yggdrasil::config.keypair.ygg_private_key.title'))
                ->rows(10)
                ->hint(trans('Yggdrasil::config.keypair.ygg_private_key.hint'));
        })->renderWithOutSubmitButton()->addButton([
                'style' => 'success',
                'name' => 'generate-key',
                'text' => trans('Yggdrasil::config.keypair.ygg_private_key.generate'),
            ])->addButton([
                'style' => 'primary',
                'type' => 'submit',
                'name' => 'submit-key',
                'text' => trans('Yggdrasil::config.keypair.ygg_private_key.submit'),
            ])->addMessage(trans('Yggdrasil::config.keypair.ygg_private_key.message'))->handle();

        if (openssl_pkey_get_private(option('ygg_private_key'))) {
            $keypairForm->addMessage(trans('Yggdrasil::config.keypair.ygg_private_key.valid'), 'success');
        } else {
            $keypairForm->addMessage(trans('Yggdrasil::config.keypair.ygg_private_key.invalid'), 'danger');
        }

        Hook::addScriptFileToPage(plugin('yggdrasil-api')->assets('config.js'));

        return view('Yggdrasil::config', [
            'forms' => ['common' => $commonForm, 'keypair' => $keypairForm],
        ]);
    }

    public function hello(Request $request, PluginManager $pluginManager)
    {
        // Default skin domain whitelist:
        // - Specified by option 'site_url'
        // - Extract host from current URL
        $extra = option('ygg_skin_domain') === '' ? [] : explode(',', option('ygg_skin_domain'));
        $skinDomains = array_map('trim', array_values(array_unique(array_merge($extra, [
            parse_url(option('site_url'), PHP_URL_HOST),
            $request->getHost(),
        ]))));

        $privateKey = openssl_pkey_get_private(option('ygg_private_key'));

        if (!$privateKey) {
            throw new IllegalArgumentException(trans('Yggdrasil::config.rsa.invalid'));
        }

        $keyData = openssl_pkey_get_details($privateKey);

        if ($keyData['bits'] < 4096) {
            throw new IllegalArgumentException(trans('Yggdrasil::config.rsa.length'));
        }

        $result = [
            'meta' => [
                'serverName' => option_localized('site_name'),
                'implementationName' => 'Yggdrasil API for Blessing Skin',
                'implementationVersion' => plugin('yggdrasil-api')->version,
                'links' => [
                    'homepage' => url('/'),
                ],
                'feature.non_email_login' => true,
            ],
            'skinDomains' => $skinDomains,
            'signaturePublickey' => $keyData['key'],
        ];

        if (!optional($pluginManager->get('disable-registration'))->isEnabled()) {
            $result['meta']['links']['register'] = url('auth/register');
        }

        return json($result);
    }

    public function logPage()
    {
        $logs = DB::table('ygg_log')->orderByDesc('time')->paginate(10);
		foreach ($logs as $user)
        {
        	$users = DB::table('users')->where('uid',$user->user_id)->value('email');
        	if($user->player_id != 0)
			{
          		$players = DB::table('players')->where('pid',$user->player_id)->value('name');        
        	}
			else
			{
          		$players =0;
        	}
          	$usna[$user->user_id] = $users;
          	$playna[$user->player_id] = $players;
        }
        $actions = trans('Yggdrasil::log.actions');
        return view('Yggdrasil::log', ['logs' => $logs, 'actions' => $actions,'usna' =>$usna ,'playna' =>$playna]);
    }

    public function generate()
    {
        try {
            return json([
                'code' => 0,
                'key' => ygg_generate_rsa_keys()['private'],
            ]);
        } catch (Exception $e) {
            return json('Error: '.$e->getMessage(), 1);
        }
    }
}
