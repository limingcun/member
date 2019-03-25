<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_role', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no');
            $table->string('name');
            $table->unsignedTinyInteger('status');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::table('m_role')->insert([
            'no'=>'R001',
            'name'=>'超级管理员',
            'status'=>1,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_role');
    }
}
