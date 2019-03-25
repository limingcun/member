<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToActive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actives', function (Blueprint $table) {
            $table->unsignedTinyInteger('type')->default(0)->comment('优惠类型（1优惠券2下单优惠）')->after('status');
            $table->unsignedInteger('admin_id')->default(0)->comment('后台用户id')->after('status');
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
            $table->dropColumn('type');
            $table->dropColumn('admin_id');
        });
    }
}
