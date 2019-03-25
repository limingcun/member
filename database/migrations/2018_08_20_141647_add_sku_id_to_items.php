<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSkuIdToItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mall_order_items', function (Blueprint $table) {
            $table->unsignedInteger('mall_sku_id')->after('mall_product_id')->default(0)->comment('sku_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mall_order_items', function (Blueprint $table) {
            $table->dropColumn('mall_sku_id');
        });
    }
}
