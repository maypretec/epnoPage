<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateBundlePartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Table definition
        Schema::create('bundle_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->references('id')->on('bundles')->nullable(false);
            $table->foreignId('epno_part_id')->references('id')->on('epno_parts')->nullable(false);
            $table->integer('qty')->default(1)->nullable(false);
            $table->boolean('status')->default(true)->nullable(false);
            $table->timestamps();
        });
        // Triggers
        DB::unprepared('CREATE TRIGGER `onCreateBundleParts_cdtSet` BEFORE INSERT ON `bundle_parts` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeBundleParts_udtUpdate` BEFORE UPDATE ON `bundle_parts` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bundle_parts');
    }
}
