<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUserProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {

            if (!Schema::hasColumn('user_profiles', 'gender')) {
                $table->string('gender', 10)->nullable()->comment('性别');
            }

            if (!Schema::hasColumn('user_profiles', 'followers_count')) {
                $table->integer('followers_count')->default(0)->comment('粉丝数');

            }
            if (!Schema::hasColumn('user_profiles', 'follows_count')) {
                $table->integer('follows_count')->default(0)->comment('关注数');
            }
            if (!Schema::hasColumn('user_profiles', 'reports_count')) {
                $table->integer('reports_count')->default(0)->comment('举报数');

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
        Schema::table('user_profiles', function (Blueprint $table) {
            //
        });
    }
}
