<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSubserviceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subservice_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subservice_id')->references('id')->on('subservices')->nullable('false');
            $table->foreignId('step_id')->references('id')->on('steps')->nullable('false');
            $table->foreignId('user_id')->references('id')->on('users')->nullable('false');
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateSubserviceLog_cdtSet` BEFORE INSERT ON `subservice_logs` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeSubserviceLog_udtUpdate` BEFORE UPDATE ON `subservice_logs` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subservice_logs');
    }
}
