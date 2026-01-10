<?php

namespace LittleSkin\YggdrasilConnect\Controllers;

use App\Services\Facades\Option;
use App\Services\Hook;
use App\Services\OptionForm;
use App\Services\PluginManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;

class ConfigController extends Controller
{
    private ClientRepository $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function render(): View
    {
        $commonForm = Option::form('common', trans('LittleSkin\\YggdrasilConnect::config.common.title'), function (OptionForm $form) {
            $form->select('ygg_uuid_algorithm', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_uuid_algorithm.title'))
                ->option('v3', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_uuid_algorithm.v3'))
                ->option('v4', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_uuid_algorithm.v4'))
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_uuid_algorithm.hint'));
            $form->text('ygg_token_expire_1', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_token_expire_1.title'));
            $form->text('ygg_token_expire_2', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_token_expire_2.title'))
                ->description(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_token_expire_2.description'));
            $form->text('ygg_tokens_limit', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_tokens_limit.title'))
                ->description(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_tokens_limit.description'));
            $form->text('ygg_rate_limit', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_rate_limit.title'))
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_rate_limit.hint'));
            $form->text('ygg_skin_domain', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_skin_domain.title'))
                ->description(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_skin_domain.description'));
            $form->text('ygg_search_profile_max', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_search_profile_max.title'))
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_search_profile_max.hint'));
            $form->checkbox('ygg_show_config_section', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_show_config_section.title'))
                ->label(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_show_config_section.label'));
            $form->checkbox('ygg_enable_ali', trans('LittleSkin\\YggdrasilConnect::config.common.ygg_enable_ali.title'))
                ->label(trans('LittleSkin\\YggdrasilConnect::config.common.ygg_enable_ali.label'));
        })->handle();

        $keypairForm = Option::form('keypair', trans('LittleSkin\\YggdrasilConnect::config.keypair.title'), function (OptionForm $form) {
            $form->textarea('ygg_private_key', trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.title'))
                ->rows(10)
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.hint'));
        })->renderWithOutSubmitButton()->addButton([
            'style' => 'success',
            'name' => 'generate-key',
            'text' => trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.generate'),
        ])->addButton([
            'style' => 'primary',
            'type' => 'submit',
            'name' => 'submit-key',
            'text' => trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.submit'),
        ])->addMessage(trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.message'))->handle();

        if (openssl_pkey_get_private(option('ygg_private_key'))) {
            $keypairForm->addMessage(trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.valid'), 'success');
        } else {
            $keypairForm->addMessage(trans('LittleSkin\\YggdrasilConnect::config.keypair.ygg_private_key.invalid'), 'danger');
        }

        $yggcForm = Option::form('yggc', 'Yggdrasil Connect', function (OptionForm $form) {
            $form->text('ygg_connect_server_url', trans('LittleSkin\\YggdrasilConnect::config.yggc.server_url.title'))
                ->description(trans('LittleSkin\\YggdrasilConnect::config.yggc.server_url.description'));
            $form->checkbox('ygg_disable_authserver', trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.title'))
                ->hint(trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.hint'))
                ->label(trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.label'))
                ->description(trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.description'));
        })->handle();

        $client = $this->clientRepository->find(env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'));

        if (!option('ygg_disable_authserver') && empty($client)) {
            $yggcForm->addMessage(trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.empty-client-id'), 'danger');
        } elseif (!$client->firstParty()) {
            $yggcForm->addMessage(trans('LittleSkin\\YggdrasilConnect::config.yggc.disable_authserver.invalid-client-id'), 'danger');
        }

        Hook::addScriptFileToPage(plugin('yggdrasil-connect')->assets('config.js'));

        return view('LittleSkin\\YggdrasilConnect::config', [
            'forms' => ['common' => $commonForm, 'keypair' => $keypairForm, 'yggc' => $yggcForm],
        ]);
    }

    public function hello(Request $request, PluginManager $pluginManager): JsonResponse
    {
        // Default skin domain whitelist:
        // - Specified by option 'site_url'
        // - Extract host from current URL
        $extra = option('ygg_skin_domain') === '' ? [] : explode(',', option('ygg_skin_domain'));
        $skinDomains = array_map('trim', array_values(array_unique(array_merge($extra, [
            parse_url(option('site_url'), PHP_URL_HOST),
            $request->getHost(),
        ]))));

        $signaturePublickey = $this->getPkey();

        $result = [
            'meta' => [
                'serverName' => option_localized('site_name'),
                'implementationName' => 'Yggdrasil Connect for Blessing Skin by LittleSkin',
                'implementationVersion' => plugin('yggdrasil-connect')->version,
                'links' => [
                    'homepage' => url('/'),
                ],
            ],
            'skinDomains' => $skinDomains,
            'signaturePublickey' => $signaturePublickey,
        ];

        if (!optional($pluginManager->get('disable-registration'))->isEnabled()) {
            $result['meta']['links']['register'] = url('auth/register');
        }

        if (!option('ygg_disable_authserver')) {
            $result['meta']['feature.non_email_login'] = true;
        }

        $yggc_server = option('ygg_connect_server_url');
        if (!empty($yggc_server)) {
            $result['meta']['feature.openid_configuration_url'] = "$yggc_server/.well-known/openid-configuration";
        }

        return json($result);
    }

    public function logPage(): View
    {
        $logs = DB::table('ygg_log')->orderByDesc('time')->paginate(10);
        $actions = trans('LittleSkin\\YggdrasilConnect::log.actions');

        return view('LittleSkin\\YggdrasilConnect::log', ['logs' => $logs, 'actions' => $actions]);
    }

    public function generate(): JsonResponse
    {
        try {
            return json([
                'code' => 0,
                'key' => ygg_generate_rsa_keys()['private'],
            ]);
        } catch (\Exception $e) {
            return json('Error: '.$e->getMessage(), 1);
        }
    }

    public function getPublicKeys(): JsonResponse
    {
        $keyData = $this->getPrivateKey();

        $publicKeyBase64 = str_replace(
            array("-----BEGIN PUBLIC KEY-----", "-----END PUBLIC KEY-----", "\n"),
            '',
            $keyData
        );

        $result = [
            'profilePropertyKeys' => array(['publicKey' => $publicKeyBase64]),
            'playerCertificateKeys' => array(['publicKey' => $publicKeyBase64]),
            'authenticationKeys' => array(['publicKey' => $publicKeyBase64]),
        ];

        return json($result);
    }

    public function getPrivateKey(): string
    {
        $privateKey = openssl_pkey_get_private(option('ygg_private_key'));

        if (!$privateKey) {
            throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::config.rsa.invalid'));
        }

        $keyData = openssl_pkey_get_details($privateKey);

        if ($keyData['bits'] < 4096) {
            throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::config.rsa.length'));
        }

        return $keyData['key'];
    }
}
