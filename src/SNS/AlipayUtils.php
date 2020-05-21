<?php

namespace haxibiao\Users\SNS;

use anerg\OAuth2\OAuth as SnsOAuth;
use haxibiao\Users\Exceptions\SNSException;

class AlipayUtils
{
    public static function userInfo($code)
    {
        $userInfo          = [];
        $_GET['auth_code'] = $code;
        $config            = [
            'app_id'      => '2021001160677587',
            'scope'       => 'auth_user',
            'pem_private' => base_path('cert/alipay/private_key'),
            'pem_public'  => base_path('cert/alipay/public_key'),
        ];
        try {
            $snsOAuth = SnsOAuth::alipay($config);
            $userInfo = $snsOAuth->userinfoRaw();
        } catch (\Exception $ex) {
            throw new SNSException($ex->getMessage(), $ex->getCode());
        }
        return $userInfo;
    }
}
