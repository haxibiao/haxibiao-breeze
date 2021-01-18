<?php

namespace Haxibiao\Breeze\Exceptions;

use Exception;

class UserException extends Exception
{
    public function __construct($msg = '', $code = 1)
    {
        parent::__construct($msg, $code);
    }
}
