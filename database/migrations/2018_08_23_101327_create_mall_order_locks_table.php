<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMallOrderLocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_order_locks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('mall_product_id')->default(0);
            $table->unsignedInteger('mall_sku_id')->default(0);
            $table->unsignedInteger('mall_order_id')->default(0)->comment('0表示未被使用');
            $table->tinyInteger('status')->default(1)->comment('1可使用2已使用3已失效4取消');
            $table->timestamp('expire_at')->nullable()->comment('锁定过期时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mall_order_locks');
    }
}
