<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentToCashFlowBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_flow_bills', function (Blueprint $table) {
            $table->decimal('payment', 8, 2)->default(0.00)->comment('实付金额');
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
            $table->dropColumn('payment');
        });
    }
}
