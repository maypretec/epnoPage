<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateComplaintClientToEpnoEvidenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaint_client_to_epno_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->references('id')->on('complaints')->nullable(false);
            $table->foreignId('user_id')->references('id')->on('users')->nullable(false);
            $table->string('client_description')->nullable(true);
            $table->string('client_file')->nullable(true);
            $table->string('client_file_name')->nullable(true);
            $table->string('epno_description')->nullable(true);
            $table->string('epno_file')->nullable(true);
            $table->string('epno_file_name')->nullable(true);
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateComplaintClientToEpnoEvidence_cdtSet` BEFORE INSERT ON `complaint_client_to_epno_evidence` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeComplaintClientToEpnoEvidence_udtUpdate` BEFORE UPDATE ON `complaint_client_to_epno_evidence` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('complaint_client_to_epno_evidence');
    }
}
