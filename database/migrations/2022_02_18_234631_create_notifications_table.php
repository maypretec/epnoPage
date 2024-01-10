<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Table definition
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->nullable(false);
            $table->foreignId('notification_type_id')->references('id')->on('notification_types')->nullable(false);
            $table->boolean('seen')->default(false)->nullable(false);
            $table->string('table_name')->nullable(false);
            $table->unsignedBigInteger('table_id')->nullable(false);
            $table->boolean('status')->default(true)->nullable(false);
            $table->timestamps();
        });
        // Triggers
        DB::unprepared('CREATE TRIGGER `onCreateNotificationscdtSet` BEFORE INSERT ON `notifications` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeNotifications_udtUpdate` BEFORE UPDATE ON `notifications` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
