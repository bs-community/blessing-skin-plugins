<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\OAuth;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use LittleSkin\YggdrasilConnect\Exceptions\BaseException;

abstract class OAuthException extends BaseException
{
    protected ?string $error_uri = null;
    protected ?string $scope = null;

    public function __construct(?string $error_description = null, ?string $scope = null)
    {
        parent::__construct($error_description ?? $this->error_description);
        $this->scope = $scope;
    }

    public function render(): Response | JsonResponse
    {
        return json($this->toArray())->setStatusCode($this->statusCode)->withHeaders([
            'WWW-Authenticate' => "Bearer error=\"$this->error\"" .
                ", error_description=\"$this->message\"" .
                (!empty($this->error_uri) ? ", error_uri=\"$this->error_uri\"" : '') .
                (!empty($this->scope) ? ", realm=\"$this->scope\"" : '')
        ]);
    }

    public function toArray(): array
    {
        $result = [
            'error' => $this->error,
            'error_description' => $this->message,
        ];

        if (!empty($this->error_uri)) {
            $result['error_uri'] = $this->error_uri;
        }

        return $result;
    }
}
