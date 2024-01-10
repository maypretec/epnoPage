<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Table definition
        Schema::create('steps', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->boolean('status')->default(true)->nullable(false);
            $table->timestamps();
        });
        // Triggers
        DB::unprepared('CREATE TRIGGER `onCreateSteps_cdtSet` BEFORE INSERT ON `steps` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeSteps_udtUpdate` BEFORE UPDATE ON `steps` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('steps');
    }
}
