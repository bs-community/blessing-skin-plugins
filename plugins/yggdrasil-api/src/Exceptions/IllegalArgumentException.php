<?php

namespace Yggdrasil\Exceptions;

class IllegalArgumentException extends YggdrasilException
{
    /**
     * Short description of the error.
     *
     * @var string
     */
    protected $error = 'IllegalArgumentException';

    /**
     * Status code of HTTP response.
     *
     * @var int
     */
    protected $statusCode = 400;
}
