<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponCodeRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_code_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('grand_id')->default(0)->index()->comment('发放记录id');
            $table->string('outer_name')->nullable()->comment('导出操作人');
            $table->datetime('outer_time')->nullable()->comment('导出时间');
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
        Schema::dropIfExists('coupon_code_records');
    }
}
