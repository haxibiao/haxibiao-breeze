<?php

namespace haxibiao\user\Exceptions;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class SignInException extends Exception implements RendersErrorsExtensions
{
    /**
     * @var @string
     */
    private $reason;

    public function __construct($msg = '', $code = 1)
    {
        parent::__construct($msg, $code);
        $this->reason = $msg;
    }

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return '登录异常';
    }

    public function extensionsContent(): array
    {
        return [
            'some'   => '后端登录检测异常',
            'reason' => $this->reason,
        ];
    }
}
