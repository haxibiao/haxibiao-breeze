<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RedesignBirthdayOnUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            //生日解藕生年、生月、生日三个字段
            if (!Schema::hasColumn('user_profiles', 'birth_on_year')) {
                $table->string('birth_on_year')->nullable()->comment('生年');
            }
            if (!Schema::hasColumn('user_profiles', 'birth_on_month')) {
                $table->string('birth_on_month')->nullable()->comment('生月');
            }
            if (!Schema::hasColumn('user_profiles', 'birth_on_day')) {
                $table->string('birth_on_day')->nullable()->comment('生日');
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
        //
    }
}
