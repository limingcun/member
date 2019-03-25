<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInviterIdToMemberCardRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_card_records', function (Blueprint $table) {
            $table->integer('inviter_id')->default(0)->comment('邀请人的user_id');
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
            $table->dropColumn('inviter_id');
        });
    }
}
