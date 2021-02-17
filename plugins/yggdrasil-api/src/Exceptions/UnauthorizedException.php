<?php

namespace Yggdrasil\Exceptions;

class UnauthorizedException extends YggdrasilException
{
    /**
     * Short description of the error.
     *
     * @var string
     */
    protected $error = 'UnauthorizedException';

    /**
     * Status code of HTTP response.
     *
     * @var int
     */
    protected $statusCode = 401;
}
