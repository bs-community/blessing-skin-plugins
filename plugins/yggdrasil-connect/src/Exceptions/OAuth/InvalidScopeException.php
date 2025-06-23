<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\OAuth;

use Illuminate\Http\Response;

class InvalidScopeException extends OAuthException
{
    protected string $error = 'invalid_scope';
    protected string $error_description = 'The requested scope is invalid, unknown, or malformed.';
    protected int $statusCode = Response::HTTP_BAD_REQUEST;
}
