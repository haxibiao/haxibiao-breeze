<?php

namespace Haxibiao\Breeze;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Overtrue\EasySms\EasySms;

class ErrorLog extends Model
{
    protected $fillable = [
        'level',
        'context',
        'message',
    ];

    const UPDATED_AT = null;

    const INFO_LEVEL    = 1;
    const ERROR_LEVEL   = 2;
    const DEFAULT_LEVEL = 0;

    public static function makeLog($context, $msg = null, $level = ErrorLog::DEFAULT_LEVEL)
    {
        if ($context instanceof Exception) {
            $contextMsg = json_encode(array_slice($context->getTrace(), 0, 10));
            $msg        = empty($msg) ? $context->getMessage() ?? '' : $msg;
        }

        return ErrorLog::create([
            'context' => $contextMsg,
            'level'   => $level,
            'message' => $msg,
        ]);
    }

    public static function error($context, $msg = null)
    {
        return ErrorLog::makeLog($context, $msg, ErrorLog::ERROR_LEVEL);
    }

    // 短信发送通知汇报错误信息
    public static function errorSms($msg = null)
    {
        if (!empty($msg)) {
            //TODO::等haxibiao-helpers包更新后，这个操作应该在smsUtil里
            $easySms = new EasySms(config('sms'));
            //先固定几个要通知的人的号码（开发、运营、财务、PM....）
            $phones = [
                "13879642286", "13810346454",
                "13575285329", "15573444905",
                "15576680468", "17608457007",
                "18682193383", "15616214339",
                "17872635502",
            ];
            foreach ($phones as $phone) {
                $easySms->send($phone, [
                    'template' => 915190,
                    'data'     => [
                        'code' => $msg,
                    ],
                ]);
            }
        }
    }
}
