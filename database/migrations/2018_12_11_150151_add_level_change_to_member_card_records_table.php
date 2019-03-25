<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLevelChangeToMemberCardRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_card_records', function (Blueprint $table) {
            $table->string('level_change', 40)->nullable()->comment('后台进行等级调整时会员的等级变化');
            $table->unsignedInteger('admin_id')->default(0)->comment('后台进行等级调整时的操作者');
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
            $table->dropColumn('level_change');
            $table->dropColumn('admin_id');
        });
    }
}
