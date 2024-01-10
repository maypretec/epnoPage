<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSupplierProposalComplaintLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_proposal_complaint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_proposal_complaint_id')->references('id')->on('supplier_proposal_complaints')->nullable(false)->index('supp_prop_cmpt_id');
            $table->foreignId('user_id')->references('id')->on('users')->nullable(false);
            $table->foreignId('step_id')->references('id')->on('steps')->nullable(false);
            $table->string('description')->nullable(false);
            $table->double('cost', 10, 2)->default(0.00)->nullable(false);
            $table->boolean('status')->default(true)->nulleable(false);            
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateSupplierProposalComplaintLog_cdtSet` BEFORE INSERT ON `supplier_proposal_complaints` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeSupplierProposalComplaintLog_udtUpdate` BEFORE UPDATE ON `supplier_proposal_complaints` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_proposal_complaint_logs');
    }
}
