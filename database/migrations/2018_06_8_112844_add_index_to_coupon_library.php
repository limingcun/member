<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToCouponLibrary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_librarys', function (Blueprint $table) {
            $table->index('name');
            $table->index('user_id');
            $table->index('status');
            $table->index('order_id');
            $table->index('coupon_id');
            $table->index('used_at');
            $table->index('period_start');
            $table->index('period_end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupon_librarys', function (Blueprint $table) {
            $table->dropIndex('name');
            $table->dropIndex('user_id');
            $table->dropIndex('status');
            $table->dropIndex('order_id');
            $table->dropIndex('coupon_id');
            $table->dropIndex('used_at');
            $table->dropIndex('period_start');
            $table->dropIndex('period_end');
        });
    }
}
