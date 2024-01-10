<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSupplierRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->nullable('false');
            $table->foreignId('supplier_proposal_id')->references('id')->on('supplier_proposals')->nullable('false');
            $table->foreignId('service_id')->references('id')->on('services')->nullable('false');
            $table->foreignId('subservice_id')->references('id')->on('subservices')->nullable('false');
            $table->tinyInteger('rating')->default(1)->nullable(false);
            $table->text('comment')->default(null)->nullable();
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateSupplierRatings_cdtSet` BEFORE INSERT ON `supplier_ratings` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeSupplierRatings_udtUpdate` BEFORE UPDATE ON `supplier_ratings` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_ratings');
    }
}
