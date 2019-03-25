<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMallExpressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_expresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shipper')->comment('配送公司');
            $table->string('shipper_code')->comment('配送公司代码');
            $table->timestamps();
        });
        $JuheExp = new \App\Services\JuheExp();
        foreach ($JuheExp->companyList()['result'] as $item){
            \App\Models\MallExpress::create([
                'shipper'=>$item['com'],
                'shipper_code'=>$item['no'],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mall_expresses');
    }
}
