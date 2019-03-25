<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponGrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_grands', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('coupon_id')->default(0)->comment('优惠券id');
            $table->datetime('grand_time')->nullable()->comment('发券时间');
            $table->tinyInteger('grand_type')->default(1)->comment('1为立即发券，2为指定时间发券');
            $table->tinyInteger('status')->default(0)->comment('发券状态');
            $table->tinyInteger('scence')->default(0)->comment('使用场景');
            $table->tinyInteger('admin_id')->default(0)->comment('发券人');
            $table->tinyInteger('chanel_type')->default(0)->comment('触达渠道');
            $table->unsignedInteger('range_type')->default(0)->comment('0为全部用户,1为指定用户,2为导入excel');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_grands');
    }
}
