<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateComplaintLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->references('id')->on('complaints')->nullable(false);
            $table->foreignId('user_id')->references('id')->on('users')->nullable(false);
            $table->foreignId('step_id')->references('id')->on('steps')->nullable(false);
            $table->string('description')->nullable(false);
            $table->double('cost', 10, 2)->default(0.00)->nullable(false);
            $table->boolean('status')->default(true)->nulleable(false);            
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateComplaintLog_cdtSet` BEFORE INSERT ON `complaint_logs` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeComplaintLog_udtUpdate` BEFORE UPDATE ON `complaint_logs` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('complaint_logs');
    }
}
