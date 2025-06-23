<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\OAuth;

use Illuminate\Http\Response;
use LittleSkin\YggdrasilConnect\Exceptions\OAuth\OAuthException;

class AccessDeniedException extends OAuthException
{
    protected string $error = 'access_denied';
    protected string $error_description = 'The user denied the authorization request.';
    protected int $statusCode = Response::HTTP_FORBIDDEN;
}
