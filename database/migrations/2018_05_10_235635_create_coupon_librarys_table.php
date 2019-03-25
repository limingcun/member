。<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponLibrarysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_librarys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('优惠券名称');
            $table->unsignedInteger('user_id')->default(0)->comment('用户id');
            $table->unsignedInteger('order_id')->default(0)->comment('订单id');
            $table->unsignedInteger('coupon_id')->default(0)->comment('优惠券id');
            $table->string('policy', 100)->nullable()->comment('优惠券领券策略');
            $table->text('policy_rule')->nullable()->comment('策略规则');
            $table->unsignedInteger('source_id')->default(0)->comment('关联id');
            $table->string('source_type', 100)->nullable()->comment('关联类型');
            $table->datetime('period_start')->nullable()->comment('有效期初始时间');
            $table->datetime('period_end')->nullable()->comment('有效期结束时间');
            $table->datetime('used_at')->nullable()->comment('使用时间');
            $table->tinyInteger('status')->default(0)->comment('核销状态');
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
        Schema::dropIfExists('coupon_librarys');
    }
}
