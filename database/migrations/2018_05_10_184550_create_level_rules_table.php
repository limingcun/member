<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLevelRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('level_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('level_id')->default(0)->comment('成长值id');
            $table->unsignedInteger('type')->default(0)->comment('1代表永不扣除，2代表某日固定日期扣除');
            $table->unsignedInteger('exp_deduction')->default(0)->comment('成长值扣除');
            $table->timestamps();
            $table->softDeletes();//deleted_at字段
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('level_rules');
    }
}
