<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('image')->nullable()->after('flag')->comment('优惠券模板图片');
            $table->tinyInteger('status')->default(0)->after('flag')->comment('模板状态0为已启动，1为已停用');
            $table->string('admin_name')->nullable()->after('image')->comment('创建人');
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
            $table->dropColumn('image');
            $table->dropColumn('status');
            $table->dropColumn('admin_name');
        });
    }
}
