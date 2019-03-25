<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLevelRuleFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('level_rules', function (Blueprint $table) {
            $table->dropColumn('level_id');
            $table->dropColumn('exp_deduction');
            $table->string('expire_time', 30)->nullable()->comment('规则失效');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
