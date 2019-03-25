<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMallSpecifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_specifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mall_product_id');
            $table->string('name')->nullable()->comment('规格名');
            $table->string('value')->nullable()->comment('规格值');
            $table->softDeletes();
            $table->timestamps();
            $table->index('name');
            $table->index('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mall_specifications');
    }
}
