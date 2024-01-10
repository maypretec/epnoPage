<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateServiceCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->references('id')->on('services')->nullable('false');
            $table->foreignId('step_id')->references('id')->on('steps')->nullable('false');
            $table->foreignId('user_id')->references('id')->on('users')->nullable('false');
            $table->string('file')->nullable();
            $table->string('file_name')->nullable();
            $table->string('comment')->nullable();
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateServiceComment_cdtSet` BEFORE INSERT ON `service_comments` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeServiceComment_udtUpdate` BEFORE UPDATE ON `service_comments` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_comments');
    }
}
