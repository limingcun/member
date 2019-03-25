<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTabToCouponLibrarysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_librarys', function (Blueprint $table) {
            $table->tinyInteger('tab')->default(0)->comment('新优惠券标识,默认0为未有新状态，1为新优惠券')->after('code');
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
            $table->dropColumn('tab');
        });
    }
}
