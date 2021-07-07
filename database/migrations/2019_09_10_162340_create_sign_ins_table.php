<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignInsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sign_ins')) {
            return;
        }

        Schema::create('sign_ins', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->comment('用户ID');
            $table->timestamps();
            $table->tinyInteger('reward_rate')->default(1)->comment('奖励倍数');

            $table->unsignedInteger('gold_reward')->default(0)->comment('智慧点奖励');
            $table->unsignedInteger('contribute_reward')->default(0)->comment('精力点奖励');
            $table->unsignedInteger('keep_signin_days')->default(0)->comment('连续签到日');

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sign_ins');
    }
}
