<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('label');
            $table->unsignedTinyInteger('status');
            $table->timestamps();
        });
        DB::table('m_permissions')->insert([
            'name'=>'会员管理',
            'label'=>'member',
            'status'=>1,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('m_permissions')->insert([
            'name'=>'积分管理',
            'label'=>'score',
            'status'=>1,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('m_permissions')->insert([
            'name'=>'会员等级管理',
            'label'=>'level',
            'status'=>1,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('m_permissions')->insert([
            'name'=>'储值管理',
            'label'=>'deposit',
            'status'=>1,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('m_permissions')->insert([
            'name'=>'意见反馈管理',
            'label'=>'feedback',
            'status'=>1,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('m_permissions')->insert([
            'name'=>'系统管理',
            'label'=>'system',
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
        Schema::dropIfExists('m_permissions');
    }
}
