<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Table definition
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->boolean('status')->default(true)->nullable(false);
            $table->timestamps();
        });
        // Triggers
        DB::unprepared('CREATE TRIGGER `onCreateRoles_cdtSet` BEFORE INSERT ON `roles` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeRoles_udtUpdate` BEFORE UPDATE ON `roles` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
