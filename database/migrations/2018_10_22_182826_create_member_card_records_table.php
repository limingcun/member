<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberCardRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_card_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID');
            $table->string('card_no', 50)->comment('卡号');
            $table->tinyInteger('cart_type')->default(0)->comment('会员卡类型');
            $table->decimal('price')->coment('会员卡价格');
            $table->date('period_start')->nullable()->comment('有效期初始时间');
            $table->date('period_end')->nullable()->comment('有效期结束时间');
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
        Schema::dropIfExists('member_card_records');
    }
}
