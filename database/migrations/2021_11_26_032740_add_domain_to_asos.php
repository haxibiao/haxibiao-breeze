<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDomainToAsos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asos', function (Blueprint $table) {

            if (!Schema::hasColumn('asos', 'domain')) {
                $table->string('domain')->nullable()->comment('域名,多APP时需要匹配子域名')->after('group');
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
        Schema::table('asos', function (Blueprint $table) {
            //
        });
    }
}
