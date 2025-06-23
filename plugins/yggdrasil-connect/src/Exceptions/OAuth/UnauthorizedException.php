<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\OAuth;

use Illuminate\Http\Response;
use LittleSkin\YggdrasilConnect\Exceptions\OAuth\OAuthException;

class UnauthorizedException extends OAuthException
{
    protected string $error_description = 'No authentication provided.';
    protected int $statusCode = Response::HTTP_UNAUTHORIZED;

    public function render(): Response
    {

        return response(null)->setStatusCode($this->statusCode)->withHeaders([
            'WWW-Authenticate' => "Bearer" .
                (!empty($this->scope) ? " realm=\"$this->scope\"" : '')
        ]);
    }
}
