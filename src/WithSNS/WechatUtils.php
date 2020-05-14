<?php

namespace Haxifang\Users\WithSNS;

class WechatUtils
{

    // 微信授权应用 appid
    public static $appId;
    // 微信授权应用 secret
    public static $secret;
    // 微信端请求超时
    public static $timeOut = 5;

    public function __construct()
    {
        self::$appId   = config('wechat_sns.appid');
        self::$secret  = config('wechat_sns.secret');
        self::$timeOut = config('wechat_sns.timeout');
    }

    /**
     * 获取微信用户 access_token
     *
     * @param [String] $code
     * @return Array
     */
    public static function accessToken($code): ?array
    {
        $accessTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';

        $response = app('client')->request('GET', $accessTokenUrl, [
            'query' => [
                'grant_type' => 'authorization_code',
                'code'       => $code,
                'appid'      => self::$appId,
                'secret'     => self::$secret,
            ],
        ]);

        $result = $response->getbody()->getContents();

        return empty($result) ? null : json_decode($result, true);
    }

    /**
     * 微信用户信息
     *
     * @param [String] $accessToken
     * @param [String] $openId
     * @return Array
     */
    public function userInfo($accessToken, $openId, $client)
    {
        $userInfoUrl = 'https://api.weixin.qq.com/sns/userinfo';
        $response    = $client->request('GET', $userInfoUrl, [
            'query' => [
                'access_token' => $accessToken,
                'openid'       => $openId,
                'lang'         => 'zh_CN',
            ],
        ]);
        $result = $response->getbody()->getContents();
        return empty($result) ? null : json_decode($result, true);
    }

}
