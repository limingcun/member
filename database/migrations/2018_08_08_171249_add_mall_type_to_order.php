<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMallTypeToOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mall_orders', function (Blueprint $table) {
            $table->tinyInteger('mall_type')
                ->after('is_express')->default(1)->comment('商品类型1为虚拟商品，2为实体商品');
            $table->string('refund_reason')->nullable()->after('form_id')->comment('退单原因');
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
            $table->dropColumn('mall_type');
            $table->dropColumn('refund_reason');
        });
    }
}
