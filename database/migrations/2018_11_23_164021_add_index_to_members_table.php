<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->index('level_id');
            $table->index('star_level_id');
            $table->index('member_type');
            $table->index('exp');
            $table->index('star_exp');
            $table->index('order_score');
            $table->index('used_score');
            $table->index('usable_score');
            $table->index('order_money');
            $table->index('expire_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('level_id');
            $table->dropIndex('star_level_id');
            $table->dropIndex('member_type');
            $table->dropIndex('exp');
            $table->dropIndex('star_exp');
            $table->dropIndex('order_score');
            $table->dropIndex('used_score');
            $table->dropIndex('usable_score');
            $table->dropIndex('order_money');
            $table->dropIndex('expire_time');
        });
    }
}
