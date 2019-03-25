<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountFeeToLibrary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_librarys', function (Blueprint $table) {
            $table->decimal('discount_fee', 8, 2)->after('code_id')->comment('优惠金额');
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
            $table->dropColumn('discount_fee');
        });
    }
}
