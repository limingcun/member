<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOrderCouponLibraryIdOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE `orders` CHANGE `coupon_library_id` `coupon_library_id` VARCHAR(255) DEFAULT '' COMMENT '订单优惠券id'");
            DB::statement("ALTER TABLE `orders` ADD `prior` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '插队数' AFTER `coupon_library_id`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
}
