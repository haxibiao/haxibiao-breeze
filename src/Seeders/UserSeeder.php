<?php
namespace Haxibiao\Breeze\Seeders;

use App\Profile;
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

        //删除荣誉的admin新账户
        $admin_email = env('MAIL_USERNAME');
        foreach (User::whereEmail($admin_email)->get() as $admin) {
            if ($admin->id > 1) {
                $admin->delete();
            }
        }

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
        $user->avatar    = '/images/avatar-' . rand(1, 15) . '.jpg';
        $user->api_token = str_random(60);
        $user->role_id   = 2; //管理员
        $user->save();
        $profile = $user->profile;
        if (empty($profile)) {
            $profile          = new Profile();
            $profile->user_id = $user->id;
        }
        $profile->save();
    }
}