<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUseLimitToMallOrderCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mall_order_coupons', function (Blueprint $table) {
            $table->tinyInteger('use_limit')->default(0)->after('product_limit')->comment('0表示全部可用，1表示自取，2表示外卖');
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
            $table->dropColumn('use_limit');
        });
    }
}
