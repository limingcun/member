<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMallOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->index()->comment('商品名称');
            $table->integer('mall_order_id')->default(0)->comment('积分商城订单号id');
            $table->integer('mall_product_id')->default(0)->comment('积分商城商品id');
            $table->string('source_type', 50)->comment('商品订单关联类型');
            $table->text('source_id')->comment('商品订单关联id');
            $table->text('remark')->nullable()->comment('商品说明');
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
        Schema::dropIfExists('mall_order_items');
    }
}
