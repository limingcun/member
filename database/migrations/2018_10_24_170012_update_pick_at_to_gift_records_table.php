<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePickAtToGiftRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gift_records', function (Blueprint $table) {
            $table->datetime('pick_at')->nullable()->comment('礼包领取时间')->change();//修改pick_at字段可以为空，类型为datetime
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gift_records', function (Blueprint $table) {
            $table->dropColumn('pick_at');
        });
    }
}
