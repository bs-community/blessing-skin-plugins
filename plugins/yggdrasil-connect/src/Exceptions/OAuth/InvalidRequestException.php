<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\OAuth;

use Illuminate\Http\Response;

class InvalidRequestException extends OAuthException
{
    protected string $error = 'invalid_request';
    protected string $error_description = 'The request is missing a required parameter, includes an unsupported parameter value, or is otherwise malformed.';
    protected int $statusCode = Response::HTTP_BAD_REQUEST;
}
