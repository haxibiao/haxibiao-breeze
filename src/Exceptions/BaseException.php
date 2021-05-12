<?php

namespace Haxibiao\Breeze\Exceptions;

use Exception;

class BaseException extends Exception
{
    public function __construct($msg = '', $code = ErrorCode::FAILURE_STATUS)
    {
        $msg = empty($msg) ? ErrorCode::getMsg($code) : $msg;

        parent::__construct($msg);
    }
}
