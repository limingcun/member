<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMallImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_images', function (Blueprint $table) {
            $table->unsignedInteger('mall_product_id');
            $table->unsignedInteger('image_id');
            $table->integer('sort')->default(0)->comment('排序字段');
            $table->primary(['mall_product_id', 'image_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mall_images');
    }
}
