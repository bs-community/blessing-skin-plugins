<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\OAuth;

use Illuminate\Http\Response;

class InvalidTokenException extends OAuthException
{
    protected string $error = 'invalid_token';
    protected string $error_description = 'The access token expired or is invalid.';
    protected int $statusCode = Response::HTTP_UNAUTHORIZED;
}
