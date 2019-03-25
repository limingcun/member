<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOriginFromToMallOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mall_orders', function (Blueprint $table) {
            $table->string('origin_from', 30)->nullable()->after('mall_type')->comment('商城订单来源，IOS是苹果，MINI是小程序');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mall_orders', function (Blueprint $table) {
            $table->dropColumn('origin_from');
        });
    }
}
