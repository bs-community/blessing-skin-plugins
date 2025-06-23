<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\OAuth;

use Illuminate\Http\Response;

class InsufficientScopeException extends OAuthException
{
    protected string $error = 'insufficient_scope';
    protected string $error_description = 'The access token provided does not contain the required scopes.'; // error_description copied from Okta
    protected int $statusCode = Response::HTTP_FORBIDDEN;
}
