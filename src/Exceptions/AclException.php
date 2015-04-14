<?php

namespace Skibish\SimpleRestAcl\Exceptions;

use Exception;

/**
 * Class AclException
 * @package Skibish\SimpleRestAcl\Exceptions
 */
class AclException extends Exception
{
    /**
     * Constructor
     *
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
