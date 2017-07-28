<?php

namespace Yggdrasil\Exceptions;

class YggdrasilException extends \Exception
{
    /**
     * Short description of the error.
     *
     * @var string
     */
    protected $error = "";

    /**
     * Longer description which can be shown to the user.
     * @var string
     */
    protected $errorMessage = "";

    /**
     * Cause of the error. Optional.
     *
     * @var string
     */
    protected $cause = "";

    /**
     * Status code of HTTP response.
     *
     * @var integer
     */
    protected $statusCode = 200;

    public function __construct($message = "Error occured.", $cause = "")
    {
        parent::__construct($message, $this->statusCode);

        $this->cause = $cause;
        $this->errorMessage = $message;

        $this->render()->send();
        exit;
    }

    protected function render()
    {
        $result = [
            'error' => $this->error,
            'errorMessage' => $this->errorMessage
        ];

        if ($this->cause !== "") {
            $result['cause'] = $this->cause;
        }

        return json($result)->setStatusCode($this->statusCode);
    }
}
