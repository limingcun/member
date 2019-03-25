<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActivityAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID');
            $table->string('name', 20)->comment('收货人');
            $table->string('phone', 20)->comment('收货人号码');
            $table->string('address', 191)->comment('地址');
            $table->tinyInteger('type')->default(0)->comment('地址类型，用于区分不同活动');
            $table->tinyInteger('status')->default(0)->comment('状态 1为正常');
            $table->string('remarks')->nullable()->comment('备注');
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
        Schema::dropIfExists('activity_addresses');
    }
}
