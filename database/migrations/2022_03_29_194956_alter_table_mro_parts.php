<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableMroParts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mro_parts', function (Blueprint $table) {
            $table->foreignId('part_no_id')->references('id')->on('part_nos')->nullable(false)->after('epno_part_id');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mro_parts', function (Blueprint $table) {
            //
        });
    }
}
