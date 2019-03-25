<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToMemberCardRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_card_records', function (Blueprint $table) {
            $table->unsignedInteger('card_code_order_id')->default(0)->comment('兑换码购卡订单id');
            $table->string('no', 10)->nullable()->comment('兑换码卡号');
            $table->string('code', 191)->nullable()->comment('兑换码');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_card_records', function (Blueprint $table) {
            $table->dropColumn('card_code_order_id');
            $table->dropColumn('no');
            $table->dropColumn('code');
        });
    }
}
