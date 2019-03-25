<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToGiftRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gift_records', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->comment('状态(0表示新礼包未被查看,1表示已被查看)');
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
            $table->dropColumn('status');
        });
    }
}
