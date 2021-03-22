<?php

namespace CrptApi\Exception;

use Exception;
use Throwable;

/**
 * Ошибка авторизации
 */
class AuthSignInException extends Exception
{
    /* @var string */
    protected $description;

    public function __construct($message, $description, $code, Throwable $previous)
    {
        $this->description = $description;

        parent::__construct($message, $code, $previous);
    }

    final public function getDescription()
    {
    }
}
