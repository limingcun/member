<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeToCouponLibrary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_librarys', function (Blueprint $table) {
            $table->string('code_id')->default('0')->index()->comment('券码id')->after('status');
            $table->string('code')->nullable()->index()->comment('兑换码')->after('status');
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
            $table->dropColumn('code_id');
            $table->dropColumn('code');
        });
    }
}
