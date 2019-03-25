<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPageToCouponGrand extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_grands', function (Blueprint $table) {
            $table->integer('page')->default(0)->comment('延迟发券页码')->after('range_type');
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
            $table->dropColumn('page');
        });
    }
}
