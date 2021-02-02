<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable(notices)){
            return;
        }
        Schema::create('notices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->comment('用户ID');
            $table->string('title')->comment('标题');
            $table->text('content')->comment('内容');
            $table->timestamp('expires_at')->nullable()->comment('到期时间');
            $table->unsignedInteger('to_user_id')->nullable()->comment('通知对象的用户ID');
            $table->string('type')->default('public_notice')->comment('通知类型');

            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notices');
    }
}
