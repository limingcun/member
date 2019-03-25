<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMemberTypeToCashFlowBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_flow_bills', function (Blueprint $table) {
            $table->tinyInteger('member_type')->default(0)->after('bill_no')->comment('会员状态:0表示go会员,1表示星球会员');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_flow_bills', function (Blueprint $table) {
            $table->dropColumn('member_type');
        });
    }
}
