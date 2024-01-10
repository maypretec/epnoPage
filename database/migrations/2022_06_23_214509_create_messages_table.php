<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->references('id')->on('conversations')->nullable(false);
            $table->foreignId('user_id')->references('id')->on('users')->nullable(false);
            $table->foreignId('step_id')->references('id')->on('steps')->nullable(false);
            $table->string('comment')->nullable(false);
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateMessage_cdtSet` BEFORE INSERT ON `messages` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeMessage_udtUpdate` BEFORE UPDATE ON `messages` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
