<?php
namespace haxibiao\user;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait AvatarHelper
{

    /**
     * 保存头像
     * @param mixed $avatar 图像链接|base64图像|UploadedFile
     *
     */
    public function saveAvatar($avatar)
    {
        $user      = $this;
        $extension = 'jpeg';
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $avatar, $res)) {
            //base64图像
            $extension     = $res[2];
            $base64_string = str_replace($res[1], '', $avatar);
            $imageStream   = base64_decode($base64_string);
        } else if ($avatar instanceof UploadedFile) {
            //UploadedFile
            $extension   = $avatar->getClientOriginalExtension();
            $imageStream = file_get_contents($avatar->getRealPath());
        } else {
            //图像链接
            $imageStream = file_get_contents($avatar);
        }

        $fileTemplate = 'avatar-%s.%s'; //以后所有cos的头像保存文件名模板
        $storePrefix  = '/storage/app/avatars/'; //以后所有cos的头像保存位置就这样了

        $avatarPath  = sprintf($storePrefix . $fileTemplate, $user->id, $extension);
        $storeStatus = Storage::cloud()->put($avatarPath, $imageStream);
        if ($storeStatus) {
            $user->update([
                'avatar' => $avatarPath,
            ]);
        }
        return $user;
    }

    /**
     * 获取头像，如果没有返回默认头像
     */
    public function getAvatarUrlAttribute()
    {
        $avatar = $this->getRawOriginal('avatar');
        if (is_null($avatar)) {
            return url(self::getDefaultAvatar());
        }

        //FIXME: 答赚的 user->avatar 字段存的还不是标准的 cos_path, 答妹已修复 “cos:%” ...
        $avatar_url = \Storage::cloud()->url($avatar);

        //一分钟内的更新头像刷新cdn
        if ($this->updated_at > now()->subSeconds(60)) {
            $avatar_url = $avatar_url . '?t=' . now()->timestamp;
        }

        return $avatar_url;
    }

    /**
     * 获取默认头像URL路径
     */
    public static function getDefaultAvatar()
    {
        //FIXME: 从 cos.haxibiao.com 获取默认头像数据,需要这个cdn准备好各种头像, 每个项目准备20个
        $cos_folder = 'avatars/' . env('APP_NAME');
        if (env('COS_DEFAULT_AVATAR') == false) {
            $cos_folder = 'avatars';
        }
        $avatar_cdn_path = sprintf($cos_folder . '/avatar-%d.png', mt_rand(1, 20));
        return "http://cos.haxibiao.com/" . $avatar_cdn_path;
    }

    /**
     * 获取QQ头像
     */
    public function getQQAvatarAttribute(): string
    {
        return 'http://q1.qlogo.cn/g?b=qq&nk=' . $this->qq . '&s=100&t=' . time();
    }
}
