<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMallProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->index()->comment('积分商城商品名称');
            $table->integer('score')->index()->default(0)->comment('所需兑换积分');
            $table->integer('store')->default(0)->comment('库存');
            $table->integer('limit_purchase')->default(0)->comment('限购数量');
            $table->string('source_type', 50)->index()->comment('多态关联类型');
            $table->integer('source_id')->comment('多态关联id');
            $table->text('remark')->nullable()->comment('商品说明');
            $table->tinyInteger('status')->index()->default(0)->comment('商品状态, 1代表已上架,2代表已下架');
            $table->datetime('shelf_time')->index()->nullable()->comment('上架时间');
            $table->integer('sold_count')->default(0)->comment('销量');
            $table->integer('sort')->default(0)->comment('产品排序');
            $table->tinyInteger('mall_type')->default(0)->comment('商品类型1为虚拟商品，2为实体商品');
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
        Schema::dropIfExists('mall_products');
    }
}
