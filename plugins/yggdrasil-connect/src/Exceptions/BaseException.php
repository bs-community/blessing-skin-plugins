<?php

namespace LittleSkin\YggdrasilConnect\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

abstract class BaseException extends \Exception
{
    // 只有 PHP 8.4 以上才能在抽象类中添加抽象属性，所以就先这样放这了
    protected string $error;
    protected string $error_description;
    protected int $statusCode;

    public function __construct(string $error_description)
    {
        parent::__construct($error_description);
        $this->error_description = $error_description;
    }

    abstract public function render(): Response|JsonResponse;

    public function report(): bool
    {
        // No need to report as it's already logged in the controller
        return true;
    }
}
