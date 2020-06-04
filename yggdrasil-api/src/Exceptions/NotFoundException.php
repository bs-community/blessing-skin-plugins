<?php

namespace Yggdrasil\Exceptions;

class NotFoundException extends YggdrasilException
{
    /**
     * Short description of the error.
     *
     * @var string
     */
    protected $error = 'NotFoundException';

    /**
     * Status code of HTTP response.
     *
     * @var int
     */
    protected $statusCode = 404;
}
