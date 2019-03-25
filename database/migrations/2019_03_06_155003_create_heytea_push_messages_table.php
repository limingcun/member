<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateistorePushMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('istore_push_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->index()->comment('用户ID');
            $table->string('title')->nullable()->comment('消息头部');
            $table->string('content')->nullable()->comment('消息体');
            $table->unsignedInteger('type')->default(0)->comment('消息类型(0表示系统推送消息,1表示订单推送消息)');
            $table->unsignedInteger('tab')->default(0)->comment('红点标识,0为未读,1为已读');
            $table->unsignedInteger('path_go')->default(0)->comment('消息推送返回数据');
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
        Schema::dropIfExists('istore_push_messages');
    }
}
