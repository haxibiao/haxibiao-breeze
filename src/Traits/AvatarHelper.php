<?php

namespace Haxibiao\Breeze\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

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

        // 头像裁剪
        $imageStream = self::reduceSize($imageStream, $extension);

        $filename = $user->id;
        if (!is_prod_env()) {
            $filename = $user->id . "." . env('APP_ENV'); //测试不覆盖线上cos文件
        }
        //example:319-timestamp.jpeg
        $filename = $filename . '-' . now()->timestamp;

        $filename   = sprintf('%s.%s', $filename, $extension);
        $avatarPath = sprintf('%s/%s', storage_folder('avatar'), $filename);

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
        $avatar = $this->getRawOriginal('avatar') ?? null;
        if (is_null($avatar)) {
            return url($this->getDefaultAvatar());
        }

        $avatar_path = parse_url($avatar, PHP_URL_PATH);
        //FIXME: breeze默认头像,就应该数据null,不要写数据库里
        if (Str::contains($avatar_path, 'images/avatar')) {
            return url($avatar_path);
        }

        //FIXME: 所有头像都不存在本地，直接上传cloud
        $avatar_url = cdnurl($avatar_path);

        //新上传头像文件名已用时间戳区分
        // if ($this->updated_at > now()->subSeconds(60)) {
        //     $avatar_url = $avatar_url . '?t=' . now()->timestamp;
        // }

        return $avatar_url;
    }

    /**
     * 获取默认头像URL路径
     */
    public function getDefaultAvatar()
    {
        $avatar_path = sprintf('/images/avatar-%d.jpg', ($this->id % 14) + 1);
        return url($avatar_path);
    }

    /**
     * 获取QQ头像
     */
    public function getQQAvatarAttribute(): string
    {
        return 'https://q1.qlogo.cn/g?b=qq&nk=' . $this->qq . '&s=100&t=' . time();
    }

    /**
     * 头像裁剪
     */
    private static function reduceSize($imageStream, $extension)
    {
        // 先实例化
        $image = Image::make($imageStream);

        $max_width  = $image->width() > 500 ? 100 : $image->width();
        $max_height = $image->height() > 500 ? 100 : $image->width();

        // 进行大小调整的操作
        $image->resize($max_width, $max_height, function ($constraint) {

            // 设定宽度是 $max_width，高度等比例双方缩放
            $constraint->aspectRatio();

            // 防止裁图时图片尺寸变大
            $constraint->upsize();
        });

        $image->encode($extension, 100);

        return $image->__toString();
    }
}
