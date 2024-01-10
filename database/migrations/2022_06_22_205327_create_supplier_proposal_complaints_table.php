<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSupplierProposalComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_proposal_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subservice_complaint_id')->references('id')->on('subservice_complaints')->nullable(false);
            $table->foreignId('supplier_proposal_id')->references('id')->on('supplier_proposals')->nullable(true);
            $table->foreignId('user_id')->references('id')->on('supplier_proposals')->nullable(false);
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateSuppPropComplaints_cdtSet` BEFORE INSERT ON `supplier_proposal_complaints` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeSuppPropComplaints_udtUpdate` BEFORE UPDATE ON `supplier_proposal_complaints` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_proposal_complaints');
    }
}
