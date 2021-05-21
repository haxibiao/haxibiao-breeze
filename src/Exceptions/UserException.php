<?php

namespace Haxibiao\Breeze\Exceptions;

use Exception;
use Haxibiao\Breeze\Exceptions\ErrorCode;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class UserException extends Exception implements RendersErrorsExtensions
{

    /**
     * @var @string
     */
    private $reason;

    public function __construct($msg = '', $code = ErrorCode::FAILURE_STATUS)
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
        return '主动异常';
    }

    public function extensionsContent(): array
    {
        return [
            'some'   => '后端异常' . gethostname(),
            'reason' => $this->reason,
            'code'   => (string) $this->code,
        ];
    }
}
