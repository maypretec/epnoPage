<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSupplierProposalLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_proposal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_proposal_id')->references('id')->on('supplier_proposals')->nullable('false');
            $table->integer('rev')->default(1)->nullable(false);
            $table->double('unitary_subtotal_cost', 10, 2)->nullable(false);
            $table->double('total_cost', 10, 2)->nullable(false);
            $table->string('quote_file')->nullable(false);
            $table->double('qty', 10, 2)->nullable(false);
            $table->integer('iva')->default(1)->nullable(false);
            $table->date('supplier_deadline')->nullable(false);
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateSupplierProposalLog_cdtSet` BEFORE INSERT ON `supplier_proposal_logs` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeSupplierProposalLog_udtUpdate` BEFORE UPDATE ON `supplier_proposal_logs` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_proposal_logs');
    }
}
