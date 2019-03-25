<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToGiftRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gift_records', function (Blueprint $table) {
            $table->date('start_at')->comment('礼包开始时间(大于等于此日期才能领取该礼包)');
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
            $table->dropColumn('start_at');
        });
    }
}
