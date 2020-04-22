<?php

namespace Yggdrasil\Exceptions;

class ForbiddenOperationException extends YggdrasilException
{
    /**
     * Short description of the error.
     *
     * @var string
     */
    protected $error = "ForbiddenOperationException";

    /**
     * Status code of HTTP response.
     *
     * @var integer
     */
    protected $statusCode = 403;
}
