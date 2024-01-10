<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSupplierProposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->references('id')->on('services')->nullable('false');
            $table->foreignId('subservice_id')->references('id')->on('subservices')->nullable('false');
            $table->foreignId('user_id')->references('id')->on('users')->nullable('false');
            $table->string('supplier_code')->nullable(true);
            $table->double('unitary_subtotal_cost', 10, 2)->nullable();
            $table->text('description')->nullable(true);
            $table->date('supplier_deadline')->nullable(true);
            $table->double('epno_cost', 10, 2)->nullable();
            $table->string('quote_file')->nullable(true);
            $table->double('total_cost', 10, 2)->nullable();
            $table->double('qty', 10, 2)->default(0)->nullable(false);
            $table->integer('iva')->default(0)->nullable(false);
            $table->integer('rev')->default(0)->nullable(false);
            $table->string('epno_po_file')->nullable(true);
            $table->boolean('is_winner')->default(2)->nulleable(false);
            $table->boolean('check')->default(false)->nulleable(false);
            $table->boolean('status')->default(true)->nulleable(false);            
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateSupplierProposal_cdtSet` BEFORE INSERT ON `supplier_proposals` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeSupplierProposal_udtUpdate` BEFORE UPDATE ON `supplier_proposals` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_proposals');
    }
}
