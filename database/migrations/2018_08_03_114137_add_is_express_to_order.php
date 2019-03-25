<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsExpressToOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mall_orders', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_express')->after('form_id')->default(0)->comment('是否有配送信息');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mall_orders', function (Blueprint $table) {
            $table->dropColumn('is_express');
        });
    }
}
