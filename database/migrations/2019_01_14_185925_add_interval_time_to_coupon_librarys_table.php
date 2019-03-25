<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIntervalTimeToCouponLibrarysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_librarys', function (Blueprint $table) {
            $table->string('interval_time', 30)->default('1')->after('period_end')->comment('优惠券有效时段');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupon_librarys', function (Blueprint $table) {
            $table->dropColumn('interval_time');
        });
    }
}
