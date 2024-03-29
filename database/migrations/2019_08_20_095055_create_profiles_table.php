<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('profiles')) {
            return;
        }
        Schema::create('profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index();

            //profile
            $table->string('qq')->nullable();
            $table->string('introduction')->default('');
            $table->string('background')->nullable()->comment('用户背景图');

            //json
            $table->json('json')->nullable();

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
            $table->integer('answers_count_today')->default(0);

            //profile
            $table->boolean('enable_tips')->default(1)->comment('开启打赏');
            $table->string('tip_words')->nullable()->comment('打赏宣传语');
            $table->tinyInteger('gender')->default(-1);
            $table->string('website')->nullable();
            $table->string('qrcode')->nullable();

            $table->unsignedInteger('age')->default(0)->comments('年龄,如果设置了生日，自动更新年龄');
            $table->string('source', 30)->index()->default('unknown')->comment('来源');
            $table->timestamp('birthday')->nullable()->comment('生日');
            $table->string('app_version')->nullable()->comment('用户最后活跃时的App版本号');

            //答题
            $table->unsignedInteger('questions_count')->index()->default(0)->comment('出题总数');
            $table->unsignedInteger('invited_count')->index()->default(0)->comment('邀请人数量');
            $table->timestamp('ad_free_expires_at')->nullable()->comment('免广告过期时间');
            $table->boolean('ad_free')->default(false)->comment('是否免广告');
            $table->unsignedInteger('answers_time_count')->index()->default(0)->comment('答题耗时总数');
            $table->unsignedInteger('answers_count')->index()->default(0)->comment('答题总数');
            $table->unsignedInteger('correct_count')->index()->default(0)->comment('答对总数');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
}