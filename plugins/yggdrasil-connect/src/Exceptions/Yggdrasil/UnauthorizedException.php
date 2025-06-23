<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil;

use Illuminate\Http\Response;

class UnauthorizedException extends YggdrasilException
{
    protected string $error = 'UnauthorizedException';
    protected string $error_description = 'The request is unauthorized.';
    protected int $statusCode = Response::HTTP_UNAUTHORIZED;
}
