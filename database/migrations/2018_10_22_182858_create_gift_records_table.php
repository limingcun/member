<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGiftRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID');
            $table->string('name', 50)->comment('礼包名称');
            $table->tinyInteger('gift_type')->default(0)->comment('礼包类型');
            $table->unsignedInteger('level_id')->default(0)->comment('升级时用户的会员等级ID');
            $table->unsignedInteger('star_level_id')->default(0)->comment('升级时用户的星球会员等级ID');
            $table->datetime('pick_at')->comment('礼包领取时间');
            $table->date('overdue_at')->comment('礼包过期时间');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_records');
    }
}
