<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvitedCountToProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('user_profiles', 'invited_count')) {
                $table->unsignedInteger('invited_count')->index()->default(0)->comment('邀请人数量');
            }
            if (!Schema::hasColumn('user_profiles', 'ad_free_expires_at')) {
                $table->timestamp('ad_free_expires_at')->nullable()->comment('免广告过期时间');
            }
            if (!Schema::hasColumn('user_profiles', 'ad_free')) {
                $table->boolean('ad_free')->default(false)->comment('是否免广告');
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