<?php

namespace Haxibiao\Breeze\Traits;

use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\User;
use Haxibiao\Breeze\Verify;
use Haxibiao\Helpers\utils\SMSUtils;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait VerifyResolvers
{
    /**
     * 发送验证码
     */
    public function sendVerifyCode($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $phone  = $args['phone'];
        $action = $args['action'];
        $qb     = User::wherePhone($phone);

        if ($qb->exists()) {
            app_track_event("发送验证码", self::getVerificationActions()[$action], $phone);
            $verify = $this->sendSMSCode($phone, $action);
        } else {
            app_track_event("发送验证码", Verify::USER_REGISTER, $phone);
            //新用户手机号注册验证码
            $verify = $this->sendLoginSMSCode($phone, $action);
        }
        return $verify;
    }

    /**
     * 效验 验证码
     */
    public function checkVerifyCode($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $sms_code = $args['code'];
        $phone    = $args['phone'];
        $action   = $args['action'];
        app_track_event("校验验证码", self::getVerificationActions()[$action], $phone);
        return $this->checkSMSCode($sms_code, $phone, $action);

    }

    public function retrievePassword($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        app_track_event('用户','验证码修改密码');
        $verify = $this->checkVerifyCode($root, $args, $context, $resolveInfo);
        $user = $verify->user;
        throw_if(!$user,GQLException::class,"用户不存在，不能修改密码！！！");
        $user->update([
            'password' => bcrypt(data_get($args,'newPassword')),
        ]);
        return $user;
    }

    public function sendSMS(array $data)
    {
        //手机短信通知
        SMSUtils::sendVerifyCode($data['phone'], $data['action']['sms'], $data['code']);
        return 1;
    }
}
