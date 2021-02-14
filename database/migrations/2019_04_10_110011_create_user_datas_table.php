<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_datas')) {
            //FIXME: 表结构不一样的项目，需要修复好依赖的字段
            return;
        }

        Schema::create('user_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique()->comment('用户ID');
            $table->json('counts')->nullable()->comments('用户关键数据统计，profile之外的，数据分析用');
            //FIXME: 修复数据后可以逐步删除这个
            $table->json('position')->nullable()->comment('用户位置信息');
            $table->json('ips')->nullable()->comment('IP信息');
            $table->json('locations')->nullable()->comment('位置信息');

            //减肥sql时，可只保留最后100条，发现问题统计维度也可以从这里挖掘
            $table->json('answers')->nullable()->comment('最后答题记录');
            $table->json('golds')->nullable()->comment('最后账单记录');

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
        Schema::dropIfExists('user_datas');
    }
}
