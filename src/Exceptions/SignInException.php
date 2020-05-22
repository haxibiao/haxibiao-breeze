<?php

namespace haxibiao\users\Exceptions;

use Exception;

class SignInException extends Exception
{
    public function __construct($msg = '', $code = 1)
    {
        parent::__construct($msg, $code);
    }
}
