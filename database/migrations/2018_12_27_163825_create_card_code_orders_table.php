<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardCodeOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_code_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->comment('用户ID');
            $table->string('name', 20)->comment('姓名');
            $table->string('phone', 20)->comment('收货人号码');
            $table->string('email', 50)->comment('邮箱');
            $table->string('address', 191)->comment('地址');
            $table->decimal('price')->comment('单价');
            $table->integer('count')->commemt('数量');
            $table->tinyInteger('card_type')->default(0)->comment('会员卡类型');
            $table->date('period_start')->nullable()->comment('会员卡有效期初始时间');
            $table->date('period_end')->nullable()->comment('会员卡有效期结束时间');
            $table->tinyInteger('status')->default(0)->comment('订单状态');
            $table->unsignedInteger('admin_id')->default(0)->comment('操作者');
            $table->timestamps();
            $table->softDeletes();
            $table->comment = '会员卡兑换码订单表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_code_orders');
    }
}
