<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPasswordStatusToCashStoragesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_storages', function (Blueprint $table) {
            $table->tinyInteger('password_status')->default(0)->comment('密码状态:0表示未设置密码,1表示已设置密码');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_storages', function (Blueprint $table) {
            $table->dropColumn('password_status');
        });
    }
}
