<?php

namespace LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil;

use Illuminate\Http\Response;

class NotFoundException extends YggdrasilException
{
    protected string $error = 'NotFoundException';
    protected string $error_description = 'The requested resource was not found.';
    protected int $statusCode = Response::HTTP_NOT_FOUND;
}
