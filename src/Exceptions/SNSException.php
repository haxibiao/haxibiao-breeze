<?php

namespace Haxifang\Users\Exceptions;

use Exception;

class SNSException extends Exception
{
    public function __construct($msg = '', $code = 1)
    {
        parent::__construct($msg, $code);
    }
}
