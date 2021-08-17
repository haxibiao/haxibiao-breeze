<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppIdToOAuthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('o_auths', function (Blueprint $table) {
            if (Schema::hasColumn('o_auths', 'app_id')) {
                $table->unsignedInteger('app_id')->default(0)->comment('授权时用的app_id');
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
        Schema::table('o_auths', function (Blueprint $table) {
            //
        });
    }
}
