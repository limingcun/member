<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNoCodeToProdution extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mall_products', function (Blueprint $table) {
            $table->string('no_code')->nullable()->after('no')->comment('商品编码');
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
            $table->dropColumn('no_code');
        });
    }
}
