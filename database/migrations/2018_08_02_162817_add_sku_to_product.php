<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSkuToProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mall_products', function (Blueprint $table) {
            $table->string('no')->nullable()->after('name')->comment('sku码');
            $table->unsignedTinyInteger('is_specification')->after('mall_type')->default(0)->comment('是否多规格');
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
            $table->dropColumn('no');
            $table->dropColumn('is_specification');
        });
    }
}
