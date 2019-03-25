<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToCouponGrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_grands', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id')->index()->comment('活动名称');
            $table->string('no')->nullable()->after('name')->index()->comment('发券活动id编码');
            $table->integer('count')->nullable()->after('amount')->comment('活动名称');
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
            $table->dropColumn('name');
            $table->dropColumn('no');
            $table->dropColumn('count');
        });
    }
}
