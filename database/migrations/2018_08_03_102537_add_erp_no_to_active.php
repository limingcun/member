<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddErpNoToActive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actives', function (Blueprint $table) {
            $table->string('erp_no')->nullable()->after('no')->comment('erp活动编码');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actives', function (Blueprint $table) {
            $table->dropColumn('erp_no');
        });
    }
}
