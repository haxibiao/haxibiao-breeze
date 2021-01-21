<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfilesColumnsToUserProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {

            //图文投稿系统统计
            if (!Schema::hasColumn('user_profiles', 'count_articles')) {
                //count
                $table->integer('count_articles')->default(0);
                $table->integer('count_likes')->default(0);
                $table->integer('count_follows')->default(0);
                $table->integer('count_followings')->default(0);
                $table->integer('count_words')->default(0);
                $table->integer('count_collections')->default(0);
                $table->integer('count_favorites')->default(0);
                $table->integer('count_actions')->default(0);
                $table->integer('count_reports')->default(0);
            }

            //profile
            if (!Schema::hasColumn('user_profiles', 'enable_tips')) {
                $table->boolean('enable_tips')->default(1)->comment('开启打赏');
                $table->string('tip_words')->nullable()->comment('打赏宣传语');
                $table->string('website')->nullable();
                $table->string('qrcode')->nullable();
            }

            if (!Schema::hasColumn('user_profiles', 'app_version')) {
                $table->string('app_version')->nullable()->comment('用户最后活跃时的App版本号');
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            //
        });
    }
}
