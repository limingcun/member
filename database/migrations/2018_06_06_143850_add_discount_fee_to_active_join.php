<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountFeeToActiveJoin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('active_joins', function (Blueprint $table) {
            $table->decimal('discount_fee', 8, 2)->after('active_id')->comment('优惠金额');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('active_joins', function (Blueprint $table) {
            $table->dropColumn('discount_fee');
            $table->dropColumn('deleted_at');
        });
    }
}
