<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAgentRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->nullable('false');
            $table->foreignId('service_id')->references('id')->on('services')->nullable('false');
            $table->tinyInteger('rating')->default(null);
            $table->text('comment')->default(null);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateAgentRatings_cdtSet` BEFORE INSERT ON `agent_ratings` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeAgentRatings_udtUpdate` BEFORE UPDATE ON `agent_ratings` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_ratings');
    }
}
