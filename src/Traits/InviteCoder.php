<?php
namespace Haxibiao\Breeze\Traits;


class InviteCoder
{
    const BASE_STR = '0BCDEFGHIJKMNPQRLSTUVWXYZ123456789';

    const STRLEN = 34;

    // 填充码不可与BASE_STR重合,否则会解码失败
    // 此处用A补位的情况是为了防止出现0000+数值ID,所以用A填充
    const FILL_CODE = 'A';

    public static function encode($id, $len = 6)
    {
        $baseStrLen = self::STRLEN;
        $code       = '';
        while ($id > 0) {
            $mod  = $id % $baseStrLen;
            $id   = (int) ($id / $baseStrLen);
            $code = self::BASE_STR[$mod] . $code;
        }
        // 当长度不够时,采取填充码进行补偿
        $code = self::attachFillCode($code, $len);

        return $code;
    }

    protected static function attachFillCode($code, $len)
    {
        return str_pad($code, $len, self::FILL_CODE, STR_PAD_LEFT);
    }

    protected static function detachFillCode($code)
    {
        if (strrpos($code, self::FILL_CODE) !== false) {
            $code = substr($code, strrpos($code, self::FILL_CODE) + 1);
        }

        return $code;
    }

    public static function decode($code)
    {
        $code  = self::detachFillCode($code);
        $len   = strlen($code);
        $code  = strrev($code);
        $value = 0;
        for ($i = 0; $i < $len; $i++) {
            $value += strpos(self::BASE_STR, $code[$i]) * pow(self::STRLEN, $i);
        }

        return $value;
    }
}