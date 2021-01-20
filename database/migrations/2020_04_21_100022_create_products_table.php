<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedInteger('store_id')->index();
            $table->double('open_time_at')->nullable();
            $table->double('close_time_at')->nullable();
            $table->unsignedInteger('category_id')->nullable()->index();
            $table->unsignedInteger('parent_id')->index()->nullable();
            $table->Integer('video_id')->nullable()->index()->comment('视频id');
            $table->string('address')->nullable();
            $table->String('name')->comment("名称:比如王者账号,衣服");

            $table->string('description')->nullable()->comment("商品描述");
            $table->unsignedInteger('price')->comment('商品价格');
            $table->unsignedInteger('available_amount')->comment('商品上架中数量');
            $table->unsignedInteger('amount')->comment('商品总数量');

            //TODO: 租号时间不同规格时：parent_id 子产品关系, 子产品需要规格字段
            $table->String('dimension')->nullable()->index()->comment("规格，维度: 大号，1小时的，XL...");
            $table->String('dimension2')->default("安卓区")->nullable()->index()->comment("安卓区，ios区。。。");
            $table->string('appointment_remark')->nullable();
            $table->string('location')->index()->nullable();
            $table->Integer('status')->default(1)->comment("1：上架，-1下架");
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
        Schema::dropIfExists('products');
    }
}
