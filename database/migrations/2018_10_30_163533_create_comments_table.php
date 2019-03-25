<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID');
            $table->tinyInteger('issue_type')->default(0)->comment('问题类型(0表示功能异常,1表示体验问题,2表示新功能建议,3表示其他)');
            $table->string('comment')->comment('反馈内容');
            $table->datetime('reply_at')->nullable()->comment('回复时间');
            $table->text('reply_text')->nullable()->comment('回复内容');
            $table->integer('admin_id')->default(0)->comment('回复人id');
            $table->tinyInteger('status')->default(0)->comment('状态(0表示未回复,1表示已回复)');
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
        Schema::dropIfExists('comments');
    }
}
