<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //部分项目已存在 dimensions 表，但是引入 package/dimensions 以它的是数据库迁移文件为主
        if(Schema::hasTable('dimensions')){
            return;
        }
        Schema::create('dimensions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index()->comment('统计的用户');
            $table->string('group', 50)->nullable()->index()->comment('维度分组');
            $table->string('name')->index()->comment('重要维度的名称');
            $table->string('date', 30)->nullable()->index()->comment('统计的时间(天)');
            $table->string('hour', 30)->nullable()->index()->comment('统计的时间(时)');
            $table->unsignedInteger('value')->default(0)->comment('值');
            $table->unsignedInteger('count')->default(1)->comment('次数');
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dimensions');
    }
}
