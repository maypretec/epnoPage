<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateVsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vs_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->nullable(false);
            $table->foreignId('vs_id')->references('id')->on('valuestreams')->nullable(false);
            $table->boolean('status')->default(true)->nullable(false);
            $table->timestamps();
        });
        
        DB::unprepared('CREATE TRIGGER `onCreateVsUsers_cdtSet` BEFORE INSERT ON `vs_users` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeVsUsers_udtUpdate` BEFORE UPDATE ON `vs_users` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vs_users');
    }
}
