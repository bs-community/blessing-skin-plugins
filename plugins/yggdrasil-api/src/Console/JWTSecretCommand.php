<?php

namespace Yggdrasil\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class JWTSecretCommand extends Command
{
    protected $signature = 'jwt:secret {--show : Display the key instead of modifying files.}';

    protected $description = 'Set the JWT secret key';

    public function handle()
    {
        $key = Str::random(64);

        if ($this->option('show')) {
            return $this->comment($key);
        }

        $this->setKeyInEnvironmentFile($key);

        $this->laravel['config']['jwt.secret'] = $key;

        $this->info("JWT secret key [$key] set successfully.");
    }

    protected function setKeyInEnvironmentFile(string $key)
    {
        $path = $this->laravel->environmentFilePath();

        file_put_contents($path, str_replace(
            'JWT_SECRET='.$this->laravel['config']['jwt.secret'],
            'JWT_SECRET='.$key,
            file_get_contents($path)
        ));
    }
}
