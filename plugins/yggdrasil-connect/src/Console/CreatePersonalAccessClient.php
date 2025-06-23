<?php

namespace LittleSkin\YggdrasilConnect\Console;

use Illuminate\Console\Command;
use Laravel\Passport\ClientRepository;

class CreatePersonalAccessClient extends Command
{
    protected $signature = 'yggc:create-personal-access-client {--name=Yggdrasil Connect Shared Client : The name of the client} {--owner=1 : The owner of the client}';
    protected $description = 'Create a new personal access client for Yggdrasil Connect.';

    public function handle(ClientRepository $clients): void
    {
        if ($clients->getPersonalAccessClientId()) {
            if (!$this->confirm('You have already set Personal Access Client ID in your .env file. Do you still want to create a new client?')) {
                $this->info('Cancelled.');

                return;
            }
        }

        $issuer = option('yggc_server_url');
        if (empty($issuer)) {
            $this->warn('You haven\'t set your Janus Root. The client won\'t be able to use with Janus.');
            $this->line('<info>You can still fix it later by setting up redirect_uri to </info><comment>{YOUR_JANUS_ROOT}/callback</comment><info> in client owner\'s [OAuth2 Apps] page</info>. (Owner UID: <comment>'.$this->option('owner').'</comment>)');
            if (!$this->confirm('Continue anyway?')) {
                $this->info('Cancelled.');

                return;
            }
        }

        $client = $clients->createPersonalAccessClient(
            $this->option('owner'),
            $this->option('name'),
            $issuer ? $issuer.'/callback' : ''
        );

        $this->info('Personal Access Client has been created successfully.');
        $this->line('<comment>Client ID:</comment> '.$client->id);
        $this->info('Please set it in your .env file as <comment>PASSPORT_PERSONAL_ACCESS_CLIENT_ID</comment>:');
        $this->line('PASSPORT_PERSONAL_ACCESS_CLIENT_ID='.$client->id);
    }
}
