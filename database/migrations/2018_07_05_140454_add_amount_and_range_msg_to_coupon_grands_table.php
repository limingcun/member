<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountAndRangeMsgToCouponGrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_grands', function (Blueprint $table) {
            $table->integer('amount')->nullable()->comment('线下派发优惠券数量')->after('page');
            $table->string('range_msg')->nullable()->comment('线下指派对象')->after('page');
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
            //
        });
    }
}
