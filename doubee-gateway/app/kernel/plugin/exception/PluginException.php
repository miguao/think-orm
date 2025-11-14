<?php

declare(strict_types=1);

namespace app\kernel\plugin\exception;

use Exception;
use Throwable;

class PluginException extends Exception
{
    public function __construct(string $message = "", int $code = 100, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}