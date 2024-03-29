<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('domain')->nullable()->comment('域名,多APP时需要匹配子域名');
            $table->string('group')->comment('功能组');
            $table->string('name')->comment('名称');
            $table->text('value')->nullable()->comment('ASO相关具体值');
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
        Schema::dropIfExists('asos');
    }
}
