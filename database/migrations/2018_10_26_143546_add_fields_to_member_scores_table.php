<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToMemberScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_scores', function (Blueprint $table) {
            $table->tinyInteger('member_type')->default(0)->after('description')->comment('0表示go会员,1表示星球会员');
            $table->tinyInteger('origin')->default(0)->after('description')->comment('0表示小程序,1表示app');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_scores', function (Blueprint $table) {
            $table->dropColumn('member_type');
            $table->dropColumn('origin');
        });
    }
}
