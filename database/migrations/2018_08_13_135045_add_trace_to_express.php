<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTraceToExpress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mall_order_expresses', function (Blueprint $table) {
            $table->text('trace')->nullable()->after('address')->comment('快递路由');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mall_order_expresses', function (Blueprint $table) {
            $table->dropColumn('trace');
        });
    }
}
