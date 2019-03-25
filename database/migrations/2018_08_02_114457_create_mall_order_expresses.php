<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMallOrderExpresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_order_expresses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mall_order_id');
            $table->unsignedInteger('address_id');
            $table->string('shipper')->nullable()->comment('配送公司');
            $table->string('shipper_code')->nullable()->comment('配送公司代码');
            $table->string('no')->nullable()->comment('订单号');
            $table->string('name');
            $table->string('phone');
            $table->string('address');
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
        Schema::dropIfExists('mall_order_expresses');
    }
}
