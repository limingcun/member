<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMallSkus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_skus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no')->nullable()->comment('编号');
            $table->unsignedInteger('mall_product_id');
            $table->unsignedInteger('store')->default(0)->comment('库存');
            $table->unsignedTinyInteger('is_show')->default(0)->comment('是否显示');
            $table->string('specificationIds')->nullable()->commnet('规格id字符串,1,2,3,4');
            $table->softDeletes();
            $table->timestamps();
            $table->index('specificationIds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mall_skus');
    }
}
