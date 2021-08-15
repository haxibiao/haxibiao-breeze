<?php
namespace Database\Seeders;

use Haxibiao\Breeze\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_DOMAIN') == null) {
            dd("请设置 网站域名 env('APP_DOMAIN')");
        }
        if (env('APP_NAME_CN') == null) {
            dd("请设置 网站中文名称 env('APP_NAME_CN')");
        }

        $admin_email = "master@" . env('APP_DOMAIN');

        $default_pass = env('DEFAULT_PASSWORD', 'dadada');

        //删除冗余的admin新账户？
        foreach (User::whereEmail($admin_email)->get() as $admin) {
            if ($admin->id > 1) {
                $admin->delete();
            }
        }

        //锁定id=1为super admin
        $admin = User::find(1);
        if (!$admin) {
            $admin = User::firstOrNew([
                'email' => $admin_email,
            ]);
        }
        $admin->email   = $admin_email;
        $admin->account = '001'; //账户名指派
        $admin->phone   = null; //未绑定手机
        $admin->name    = env('APP_NAME_CN');
        $pass           = $default_pass;

        $admin->password  = bcrypt($pass);
        $admin->api_token = str_random(60);
        $admin->role_id   = User::ADMIN_STATUS; //管理员
        $admin->save();
        echo "\n已初始化账户:" . $admin->email;

        //锁定id=2 为 小编
        $editor_email = 'editor@' . env('APP_DOMAIN');
        $editor       = User::find(2);
        if (!$editor) {
            $editor = User::firstOrNew([
                'email' => $editor_email,
            ]);
        }
        $editor->email     = $editor_email;
        $admin->account    = '002'; //账户名指派
        $admin->phone      = null; //未绑定手机
        $editor->name      = env('APP_NAME_CN') . "小编";
        $pass              = $default_pass;
        $editor->password  = bcrypt($pass);
        $editor->api_token = str_random(60);
        $editor->role_id   = User::EDITOR_STATUS; //小编
        $editor->save();
        echo "\n已初始化账户:" . $editor->email;

        //锁定id=3 为 测试用户
        $user_email = 'user@' . env('APP_DOMAIN');
        $user       = User::find(3);
        if (!$user) {
            $user = User::firstOrNew([
                'email' => $user_email,
            ]);
        }
        $user->email     = $user_email;
        $admin->account  = '003'; //账户名指派
        $admin->phone    = null; //未绑定手机
        $user->name      = env('APP_NAME_CN') . "用户";
        $pass            = $default_pass;
        $user->password  = bcrypt($pass);
        $user->api_token = str_random(60);
        //role_id 默认为普通用户
        $user->save();

        echo "\n已初始化账户:" . $user->email . "\n";

    }
}
