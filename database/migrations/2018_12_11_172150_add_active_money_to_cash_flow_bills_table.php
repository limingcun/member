<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveMoneyToCashFlowBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_flow_bills', function (Blueprint $table) {
            $table->decimal('active_money', 8, 2)->default(0.00)->after('free_money')->comment('活动金额');
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
            $table->dropColumn('active_money');
        });
    }
}
