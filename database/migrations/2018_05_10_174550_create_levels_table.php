<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 45)->comment('等级名称');
            $table->unsignedInteger('exp_min')->default(0)->comment('最低成长值');
            $table->unsignedInteger('exp_max')->default(0)->comment('最高成长值');
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
        Schema::dropIfExists('levels');
    }
}
