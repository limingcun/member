<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUseLimitToCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->tinyInteger('use_limit')->default(0)->after('flag')->comment('0表示全部可用，1表示自取，2表示外卖');
        });
        Schema::table('coupon_librarys', function (Blueprint $table) {
            $table->tinyInteger('use_limit')->default(0)->after('code_id')->comment('0表示全部可用，1表示自取，2表示外卖');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('use_limit');
        });
        Schema::table('coupon_librarys', function (Blueprint $table) {
            $table->dropColumn('use_limit');
        });
    }
}
