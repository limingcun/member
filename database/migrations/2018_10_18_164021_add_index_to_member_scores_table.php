<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToMemberScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_scores', function (Blueprint $table) {
            if ($this->hasIndex('member_scores','user_id')){
                $table->dropIndex(['user_id']);
            }
            if ($this->hasIndex('member_scores','source_id')){
                $table->dropIndex(['source_id']);
            }
            if ($this->hasIndex('member_scores','created_at')){
                $table->dropIndex(['created_at']);
            }
            $table->index('user_id');
            $table->index('source_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_scores', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('source_id');
            $table->dropIndex('created_at');
        });
    }
    
    /*
     * 判断索引是否存在
     */
    public function hasIndex($table, $name)
    {
        $name = $table.'_'.$name.'_index';
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $doctrineTable = $dbSchemaManager->listTableDetails($table);
        return $doctrineTable->hasIndex($name);
    }
}
