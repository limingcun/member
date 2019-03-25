<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeIdToMallOrderCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mall_order_coupons', function (Blueprint $table) {
            $table->string('code_id')->default('0')->index()->comment('优惠券编码id')->after('product_limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mall_order_coupons', function (Blueprint $table) {
            $table->dropColumn('code_id');
        });
    }
}
