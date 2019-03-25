<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPeriodColumnToCouponGrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_grands', function (Blueprint $table) {
            $table->unsignedTinyInteger('period_type')->default(0)->after('range_type')->comment('过期类型（0绝对时间，1相对时间）');
            $table->datetime('period_start')->nullable()->after('period_type')->comment('有效期初始时间');
            $table->datetime('period_end')->nullable()->after('period_start')->comment('有效期结束时间');
            $table->unsignedInteger('period_day')->default(0)->after('period_end')->comment('有效时间段');
            $table->tinyInteger('unit_time')->default(0)->after('period_day')->comment('时间维度单位,0表示天,1表示月，2表示年');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupon_grands', function (Blueprint $table) {
            $table->dropColumn('period_type');
            $table->dropColumn('period_start');
            $table->dropColumn('period_end');
            $table->dropColumn('period_day');
            $table->dropColumn('unit_time');
        });
    }
}
