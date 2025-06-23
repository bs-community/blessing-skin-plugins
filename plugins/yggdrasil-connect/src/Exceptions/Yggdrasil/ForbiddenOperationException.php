<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil;

use Illuminate\Http\Response;

class ForbiddenOperationException extends YggdrasilException
{
    protected string $error = 'ForbiddenOperationException';
    protected string $error_description = 'The requested operation is forbidden.';
    protected int $statusCode = Response::HTTP_FORBIDDEN;
}
