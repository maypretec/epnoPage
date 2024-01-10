<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreatePartNosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Table definition
        Schema::create('part_nos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('supplier_partno')->nullable(false);
            $table->integer('max_qty')->default(1)->nullable(false);
            $table->integer('min_qty')->default(1)->nullable(false);
            $table->integer('current_qty')->default(1)->nullable(false);
            $table->double('price')->default(0.0)->nullable(false);
            $table->foreignId('part_category_id')->references('id')->on('part_categories')->nullable(true);
            $table->foreignId('epno_part_id')->references('id')->on('epno_parts')->nullable(true);
            $table->foreignId('user_id')->references('id')->on('users')->nullable(false);
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });
        
        // Triggers
        DB::unprepared('CREATE TRIGGER `onCreatePartNos_cdtSet` BEFORE INSERT ON `part_nos` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangePartNos_udtUpdate` BEFORE UPDATE ON `part_nos` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part_nos');
    }
}
