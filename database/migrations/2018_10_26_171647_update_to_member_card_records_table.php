<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateToMemberCardRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_card_records', function (Blueprint $table) {
            $table->tinyInteger('card_type')->default(0)->comment('会员卡类型');
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
            $table->dropColumn('cart_type');
        });
    }
}
