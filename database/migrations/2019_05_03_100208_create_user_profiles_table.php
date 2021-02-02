<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_profiles')) {
            //FIXME: 表结构不一样的项目，需要修复好依赖的字段
            return;
        }

        Schema::create('user_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique()->index()->comment('用户ID');

            //infomation
            $table->unsignedInteger('age')->default(0)->comments('年龄');
            $table->string('source', 30)->index()->default('unknown')->comment('下载来源');
            $table->json('position')->nullable()->comment('用户位置信息');
            $table->string('introduction')->default('')->comment('介绍');
            $table->timestamp('birthday')->nullable()->comment('生日');
            $table->string('sub_name', 30)->nullable()->comment('头衔');
            $table->string('qq')->nullable();
            $table->string('background')->nullable()->comment('用户背景图');
            $table->json('json')->nullable();

            //答题统计字段
            $table->integer('total_contributes')->index()->default(0)->comment('用户总贡献');
            $table->unsignedInteger('transaction_sum_amount')->default(0)->comment('提现总额'); //已淘汰，替换为 wallet->total_withdraw_amount
            $table->unsignedInteger('visited_count')->default(0)->comment('访问统计');
            $table->unsignedInteger('questions_count')->index()->default(0)->comment('出题总数');
            $table->unsignedInteger('curations_count')->index()->default(0)->comment('纠题总数');
            $table->unsignedInteger('reports_count')->index()->default(0)->comment("举报数");
            $table->unsignedInteger('reports_correct_count')->index()->default(0)->comment("举报成功数");
            $table->unsignedInteger('answers_count')->index()->default(0)->comment('答题总数');
            $table->unsignedInteger('correct_count')->index()->default(0)->comment('答对总数');
            $table->unsignedInteger('today_reward_video_count')->default(0)->comment('今日激励视频次数');

            $table->integer('posts_count')->nullable()->comment('视频动态数');
            $table->integer('comments_count')->nullable()->comment('评论数');
            $table->integer('likes_count')->nullable()->comment('点赞数');

            $table->integer('followers_count')->index()->default(0)->comment('粉丝数');
            $table->integer('follows_count')->index()->default(0)->comment('关注数');

            //答题项目 时间字段
            $table->unsignedInteger('pay_info_change_count')->default(0)->comment('提现信息变更次数');
            $table->timestamp('pay_info_change_at')->nullable()->comment('提现信息变更时间');
            $table->timestamp('verified_at')->nullable()->comments('验证时间');
            $table->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            $table->timestamp('last_reward_video_time')->nullable()->comment('最后激励视频时间');

            //答题 比率
            $table->double('question_pass_rate')->index()->default(0)->comment('出题通过率');
            $table->double('curation_pass_rate')->index()->default(0)->comment('纠题通过率');
            $table->double('answer_correct_rate')->index()->default(0)->comment('答题正确率');
            $table->double('create_question_answer_correct_rate')->index()->default(0)->comment('出题答对率');
            $table->double('question_curation_rate')->index()->default(0)->comment('题目纠题率');

            //客户端
            $table->string('os', 10)->nullable()->comment('系统');
            $table->string('device_id')->nullable()->index()->comment('设备uuid');
            $table->string('version', 30)->default('unknow')->comment('版本');
            $table->string('package', 30)->default('unknow')->comment('渠道的APP包名');

            //答题
            $table->unsignedInteger('answers_count_today')->default(0)->index()->comment('今日答题数');

            //已移动到 user_datas
            // $table->json('golds')->nullable()->comment('智慧点记录');
            // $table->json('answers')->nullable()->comment('答题记录');

            //分红
            $table->decimal('total_bonus_earnings')->default(0)->comment('分红总收益');
            $table->unsignedInteger('keep_signin_days')->default(0)->comment('连续签到日');

            $table->tinyInteger('success_withdraw_counts')->nullable()->comment('用户成功提现次数 0:提现0次；1:提现1次；2:提现 2-7 次；3:提现7次以上');

            // 印象视频
            $table->integer('count_articles')->default(0);
            $table->integer('count_likes')->default(0);
            $table->integer('count_follows')->default(0);
            $table->integer('count_followings')->default(0);
            $table->integer('count_words')->default(0);
            $table->integer('count_collections')->default(0);
            $table->integer('count_favorites')->default(0);
            $table->integer('count_actions')->default(0);
            $table->integer('count_reports')->default(0);
            $table->integer('count_contributes')->default(0)->comment('用户贡献点');
            $table->boolean('enable_tips')->default(1)->comment('开启打赏');
            $table->string('tip_words')->nullable()->comment('打赏宣传语');
            $table->tinyInteger('gender')->default(-1);
            $table->string('website')->nullable();
            $table->string('qrcode')->nullable();
            $table->string('app_version')->nullable()->comment('用户最后活跃时的App版本号');
            $table->unsignedInteger('keep_checkin_days')->default(0)->comment('连续签到日');


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
        Schema::dropIfExists('user_profiles');
    }
}
