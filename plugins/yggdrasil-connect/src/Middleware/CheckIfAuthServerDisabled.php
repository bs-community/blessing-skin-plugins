<?php

namespace LittleSkin\YggdrasilConnect\Middleware;

use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\ForbiddenOperationException;

class CheckIfAuthServerDisabled
{
    private ClientRepository $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function handle(Request $request, \Closure $next)
    {
        if (option('ygg_disable_authserver')) {
            throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.authserver-disabled'));
        }

        $client = $this->clientRepository->find(env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'));

        if (empty($client) || !$client->personal_access_client) {
            throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.yggc.invalid-client-id'));
        }

        return $next($request);
    }
}
