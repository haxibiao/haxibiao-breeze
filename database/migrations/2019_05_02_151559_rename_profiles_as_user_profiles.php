<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameProfilesAsUserProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //统一使用UserProfile来做 Profile关系，兼容答赚，但是不丢失以前 profiles表里的数据，缺少字段的后面breeze升级的时候检查字段缺失..

        //故意修改migrations 时间到 create_user_profiles_table之前，避免新创建的表全是空数据
        if (Schema::hasTable('profiles') && !Schema::hasTable('user_profiles')) {
            Schema::rename('profiles', 'user_profiles');
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
