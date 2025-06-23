<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil;

use Illuminate\Http\Response;

class IllegalArgumentException extends YggdrasilException
{
    protected string $error = 'IllegalArgumentException';
    protected string $error_description = 'The provided argument is invalid.';
    protected int $statusCode = Response::HTTP_BAD_REQUEST;
}
