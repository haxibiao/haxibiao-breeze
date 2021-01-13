<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixBreezeColumnsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'account')) {
                $table->string('account')->unique()->comment("账户名字段,兼容答赚");
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->comment("手机号");
            }

            if (!Schema::hasColumn('users', 'uuid')) {
                $table->string('uuid')->nullable()->comment("UUID");
            }

            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->comment("头像URL或者Path");
            }
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->tinyInteger('role_id')->default(0)->index()->comment("角色id：0 用户，1 编辑 2 管理");
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->tinyInteger('status')->default(0)->index()->comment("状态：0 默认，-1 禁用 ..");
            }
            if (!Schema::hasColumn('users', 'json')) {
                $table->json('json')->nullable()->comment("NO SQL 数据");
            }

            if (!Schema::hasColumn('users', 'count_comments')) {
                $table->integer('count_comments')->default(0)->comment('评论数');
            }
            if (!Schema::hasColumn('users', 'count_tips')) {
                $table->integer('count_tips')->default(0)->comment('打赏数');
            }
            if (!Schema::hasColumn('users', 'count_posts')) {
                $table->integer('count_posts')->default(0)->comment('动态数');

            }

            if (!Schema::hasColumn('users', 'introduction_tips')) {
                $table->string('introduction_tips')->nullable()->comment("打赏板块个人介绍");
            }
            if (!Schema::hasColumn('users', 'is_tips')) {
                $table->tinyInteger('is_tips')->default(0)->comment("是否开启打赏");
            }
            if (!Schema::hasColumn('users', 'ticket')) {
                $table->integer('ticket')->default(180)->comment("精力点");
            }
            if (!Schema::hasColumn('users', 'gold')) {
                $table->integer('gold')->default(0)->comment("金币");
            }
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
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
        Schema::table('users', function (Blueprint $table) {

        });
    }
}
