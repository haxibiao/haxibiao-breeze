<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRechargesTable extends Migration
{
    /**
     * 创建充值表
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recharges', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->index()->comment('充值用户id');
            $table->string('title', 100)->comment('充值标题');
            $table->string('trade_no')->index()->comment('内部交易订单号');
            $table->tinyInteger('status')->default(0)->comment('-1:充值失败 0:待支付 1:充值成功(支付成功)');
            $table->decimal('amount', 60)->comment('交易金额');
            $table->string('platform', 50)->index()->comment('交易平台');
            $table->json('data')->nullable()->comment('交易平台回调信息');
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
        Schema::dropIfExists('recharges');
    }
}
