<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnionIdToOAuthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('o_auths', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('o_auths', 'union_id')) {
                $table->string('union_id', 30)->default('');
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
