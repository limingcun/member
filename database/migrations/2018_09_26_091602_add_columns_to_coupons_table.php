<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->tinyInteger('material_limit')->default(0)->after('shop_limit')->comment('加料限制');
            $table->tinyInteger('category_limit')->default(0)->after('shop_limit')->comment('饮品类别限制');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('material_limit');
            $table->dropColumn('category_limit');
        });
    }
}
