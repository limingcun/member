<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashStoragesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_storages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->index()->comment('用户ID');
            $table->date('storage_start')->comment('储值开始时间');
            $table->tinyInteger('storage_way')->default(0)->comment('储值方式:0表示Go小程序,1表示APP');
            $table->tinyInteger('status')->default(0)->comment('状态:0表示正常,1表示禁用');
            $table->tinyInteger('consume_way')->default(0)->comment('消费方式:0表示GO小程序,1表示门店');
            $table->decimal('total_money', 8, 2)->default(0.00)->comment('累计充值金额');
            $table->decimal('consume_money', 8, 2)->default(0.00)->comment('累计消费金额');
            $table->decimal('free_money', 8, 2)->default(0.00)->comment('余额');
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
        Schema::dropIfExists('cash_storages');
    }
}
