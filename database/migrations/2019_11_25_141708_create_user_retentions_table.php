<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRetentionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	if(!Schema::hasTable('user_retentions')){
			Schema::create('user_retentions', function (Blueprint $table) {
				$table->bigIncrements('id');
				$table->integer('user_id')->index()->comment('用户ID');
				$table->timestamp('day2_at')->nullable()->index()->comment('次日留存');

				$table->timestamp('day3_at')->nullable()->index()->comment('三日留存');
				$table->timestamp('day5_at')->nullable()->index()->comment('五日留存');
				$table->timestamp('day7_at')->nullable()->index()->comment('七日留存');
				$table->timestamp('day30_at')->nullable()->index()->comment('月留存');

				//新增凑齐前7天连续跟踪留存，算周留存率可用
				$table->timestamp('day4_at')->nullable()->index()->comment('4日留存');
				$table->timestamp('day6_at')->nullable()->index()->comment('6日留存');

				$table->timestamps();
			});
			return;
		}
		// 兼容旧表
		Schema::table('user_retentions', function (Blueprint $table) {
			if(Schema::hasColumn('user_retentions','next_day_retention_at')){
				$table->renameColumn('next_day_retention_at','day2_at');
			} else if(Schema::hasColumn('user_retentions','day2_at')){
				return;
			}else{
				$table->timestamp('day2_at')->nullable()->index()->comment('次日留存');
			}

			if(Schema::hasColumn('user_retentions','third_day_retention_at')){
				$table->renameColumn('third_day_retention_at','day3_at');
			} else {
				$table->timestamp('day3_at')->nullable()->index()->comment('三日留存');
			}

			if(Schema::hasColumn('user_retentions','fifth_day_retention_at')){
				$table->renameColumn('fifth_day_retention_at','day4_at');
			} else {
				$table->timestamp('day4_at')->nullable()->index()->comment('4日留存');
			}

			if(!Schema::hasColumn('user_retentions','day5_at')){
				$table->timestamp('day5_at')->nullable()->index()->comment('五日留存');
			}

			if(Schema::hasColumn('user_retentions','sixth_day_retention_at')){
				$table->renameColumn('sixth_day_retention_at','day6_at');
			} else {
				$table->timestamp('day6_at')->nullable()->index()->comment('6日留存');
			}

			if(!Schema::hasColumn('user_retentions','day7_at')){
				$table->timestamp('day7_at')->nullable()->index()->comment('七日留存');
			}

			if(Schema::hasColumn('user_retentions','month_retention_at')){
				$table->renameColumn('month_retention_at','day30_at');
			} else {
				$table->timestamp('day30_at')->nullable()->index()->comment('月留存');
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
        Schema::dropIfExists('user_retentions');
    }
}
