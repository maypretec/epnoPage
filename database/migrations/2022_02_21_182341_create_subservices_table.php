<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSubservicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subservices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->references('id')->on('services')->nullable('false');
            $table->string('name')->nullable(false);
            $table->foreignId('step_id')->references('id')->on('steps')->nullable('false');
            $table->date('epno_deadline')->nullable();
            $table->double('qty', 10, 2)->nullable(false);
            $table->foreignId('category_id')->references('id')->on('categories')->nullable('false');
            $table->string('specs_file')->nullable();
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateSubservice_cdtSet` BEFORE INSERT ON `subservices` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeSubservice_udtUpdate` BEFORE UPDATE ON `subservices` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subservices');
    }
}
