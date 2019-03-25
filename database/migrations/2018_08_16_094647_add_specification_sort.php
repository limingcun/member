<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpecificationSort extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mall_products', function (Blueprint $table) {
            $table->text('specification_sort')->nullable()->after('is_specification')->comment('规格排序字段');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mall_products', function (Blueprint $table) {
            $table->dropColumn('specification_sort');
        });
    }
}
