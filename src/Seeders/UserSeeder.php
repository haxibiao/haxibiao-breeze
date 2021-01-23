<?php
namespace Haxibiao\Breeze\Seeders;

use App\User;
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
        if (env('DB_PASSWORD') == null) {
            dd("请设置 超管密码 env('DB_PASSWORD')");
        }
        if (env('APP_NAME_CN') == null) {
            dd("请设置 网站中文名称 env('APP_NAME_CN')");
        }

        $admin_email = env('MAIL_USERNAME');

        // //删除冗余的admin新账户？
        // foreach (User::whereEmail($admin_email)->get() as $admin) {
        //     if ($admin->id > 1) {
        //         $admin->delete();
        //     }
        // }

        //锁定id=1为super admin
        $user = User::find(1);
        if (!$user) {
            $user = User::firstOrNew([
                'email' => $admin_email,
            ]);
        }
        $user->email   = $admin_email;
        $user->account = $user->email;
        $user->phone   = $user->email;
        $user->name    = env('APP_NAME_CN');
        $pass          = env('DB_PASSWORD', 'REDIS_PASSWORD');

        $user->password  = bcrypt($pass);
        $user->api_token = str_random(60);
        $user->role_id   = User::ADMIN_STATUS; //管理员
        $user->save();
        //profile 属性会自动lazy load ...

        //锁定id=2 为 小编
        $editor = User::find(2);
        if (!$editor) {
            $editor = User::firstOrNew([
                'email' => $admin_email,
            ]);
        }
        $editor->email     = 'editor@breeze.com';
        $editor->account   = $user->email;
        $editor->phone     = $user->email;
        $editor->name      = env('APP_NAME_CN') . "小编";
        $pass              = env('DB_PASSWORD', 'REDIS_PASSWORD');
        $editor->password  = bcrypt($pass);
        $editor->api_token = str_random(60);
        $editor->role_id   = User::EDITOR_STATUS; //小编
        $editor->save();

        //锁定id=3 为 测试用户
        $user = User::find(3);
        if (!$user) {
            $user = User::firstOrNew([
                'email' => $admin_email,
            ]);
        }
        $user->email     = 'user@breeze.com';
        $user->account   = $user->email;
        $user->phone     = $user->email;
        $user->name      = env('APP_NAME_CN') . "用户";
        $pass            = env('DB_PASSWORD', 'REDIS_PASSWORD');
        $user->password  = bcrypt($pass);
        $user->api_token = str_random(60);
        //role_id 默认为普通用户
        $user->save();

    }
}
