<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nulleable(false);
            $table->string('rfc')->nulleable(false);
            $table->foreignId('colony_id')->references('id')->on('colonies')->nullable(false);
            $table->string('street')->nullable(false);
            $table->string('external_number')->nullable(false);
            $table->string('internal_number')->nullable(true);
            $table->string('logo')->default('')->nulleable(true);
            $table->string('url')->nullable(true);
            $table->string('pay_days')->nullable(true);
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });

        // Triggers
        DB::unprepared('CREATE TRIGGER `onCreateOrganizations_cdtSet` BEFORE INSERT ON `organizations` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeOrganizations_udtUpdate` BEFORE UPDATE ON `organizations` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organizations');
    }
}
