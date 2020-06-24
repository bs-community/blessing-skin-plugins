<?php

declare(strict_types=1);

namespace Blessing\FixV4Update;

use Exception;

class PackageManager extends \App\Services\PackageManager
{
    public function __construct(\GuzzleHttp\Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function download(string $url, string $path, $shasum = null): self
    {
        $this->path = $path;
        try {
            $this->guzzle->request('GET', $url, [
                'sink' => $path,
                'verify' => resource_path('misc/ca-bundle.crt'),
            ]);
        } catch (Exception $e) {
            throw new Exception(trans('admin.download.errors.download', ['error' => $e->getMessage()]));
        }

        if (is_string($shasum) && sha1_file($path) != $shasum) {
            @unlink($path);
            throw new Exception(trans('admin.download.errors.shasum'));
        }

        return $this;
    }

    public function progress(): float
    {
        return 0.0;
    }
}
