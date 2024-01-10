<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateEpnoPartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Table definition
        Schema::create('epno_parts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('part_no')->nullable(false)->unique();
            $table->string('image')->nullable(false);
            $table->foreignId('unit_id')->references('id')->on('units')->nullable(false);
            $table->foreignId('part_category_id')->references('id')->on('part_categories')->nullable(false);
            $table->boolean('status')->default(true)->nullable(false);
            $table->timestamps();
        });
        
        // Triggers
        DB::unprepared('CREATE TRIGGER `onCreateEpnoParts_cdtSet` BEFORE INSERT ON `epno_parts` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeEpnoParts_udtUpdate` BEFORE UPDATE ON `epno_parts` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('epno_parts');
    }
}
