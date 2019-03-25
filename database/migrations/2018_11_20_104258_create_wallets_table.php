<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cash')->default(0)->comment('金额');
            $table->tinyInteger('way')->default(0)->comment('营销方式：0表示无营销活动,1表示满赠,2表示满减');
            $table->integer('way_rech')->default(0)->comment('充值金额');
            $table->integer('way_free')->default(0)->comment('优惠金额');
            $table->integer('way_limit')->default(0)->comment('限制次数');
            $table->tinyInteger('period_type')->default(0)->comment('有效期：0表示永久有效,1表示固定时期,2表示固定时长');
            $table->date('period_start')->nullable()->comment('开始时间');
            $table->date('period_end')->nullable()->comment('结束时间');
            $table->date('period_day')->default(0)->comment('固定时长');
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
        Schema::dropIfExists('wallets');
    }
}
