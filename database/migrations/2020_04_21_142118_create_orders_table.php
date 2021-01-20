<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('orders');
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('store_id')->index()->nullable();
            $table->string('reserve_phone')->nullable()->index()->comment('预定存放的手机号');
            $table->timestamp('reserve_datetime')->nullable()->comment('预定时间');
            $table->string('remark')->nullable()->comment('备注');
            $table->string('trade_no')->index()->comment("订单号");
            $table->double('pay_amount')->nullable()->comment('支付金额');
            $table->integer('status')->comment("-3：商家未响应 -2:已取消 -1:已过期, 0:待商家接单（确认）, 1:待用户消费(商家已接单) 2:已正常完成消费");
            $table->string('verify_code')->nullable()->comment('订单完成验证码');

            $table->json('data')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
