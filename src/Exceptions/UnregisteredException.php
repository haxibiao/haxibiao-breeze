<?php

namespace Haxibiao\Breeze\Exceptions;


use Exception;

class UnregisteredException extends Exception
{
    function __construct($msg='')
    {
        parent::__construct($msg);
    }
}
