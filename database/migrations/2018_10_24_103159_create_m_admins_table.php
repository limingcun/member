<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no');
            $table->string('name');
            $table->string('email');
            $table->unsignedTinyInteger('sex');
            $table->string('mobile');
            $table->string('department');
            $table->string('password');
            $table->unsignedInteger('role_id');
            $table->unsignedTinyInteger('status');
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
        Schema::dropIfExists('m_admins');
    }
}
