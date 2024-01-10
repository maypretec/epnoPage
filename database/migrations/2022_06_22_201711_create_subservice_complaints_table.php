<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSubserviceComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subservice_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->references('id')->on('complaints')->nullable(false);
            $table->foreignId('subservice_id')->references('id')->on('subservices')->nullable(false);
            $table->boolean('status')->default(true)->nulleable(false);

            $table->timestamps();
        });
        DB::unprepared('CREATE TRIGGER `onCreateServiceComplaints_cdtSet` BEFORE INSERT ON `subservice_complaints` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeServiceComplaints_udtUpdate` BEFORE UPDATE ON `subservice_complaints` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subservice_complaints');
    }
}
