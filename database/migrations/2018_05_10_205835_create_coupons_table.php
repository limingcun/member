<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('优惠券名称');
            $table->string('policy', 100)->nullable()->comment('优惠券领券策略');
            $table->text('policy_rule')->nullable()->comment('策略规则');
            $table->unsignedTinyInteger('period_type')->default(0)->comment('过期类型（1绝对时间，2相对时间）');
            $table->datetime('period_start')->nullable()->comment('有效期初始时间');
            $table->datetime('period_end')->nullable()->comment('有效期结束时间');
            $table->unsignedInteger('period_day')->default(0)->comment('有效时间段');
            $table->unsignedInteger('count')->default(0)->comment('发券数量');
            $table->unsignedTinyInteger('shop_limit')->default(0)->comment('门店限制');
            $table->unsignedTinyInteger('product_limit')->default(0)->comment('商品限制');
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
        Schema::dropIfExists('coupons');
    }
}
