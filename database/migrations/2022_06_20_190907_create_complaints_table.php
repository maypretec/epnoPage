<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreateComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->references('id')->on('orders')->nullable(false);
            $table->foreignId('service_id')->references('id')->on('services')->nullable(false);
            $table->string('title')->nullable(false);
            $table->foreignId('user_id')->references('id')->on('users')->nullable(false);
            $table->foreignId('organization_id')->references('id')->on('organizations')->nullable(false);
            $table->string('complaint_num')->nullable(false);
            $table->string('responsible_user')->nullable(false);
            $table->string('order_num')->nullable(false);
            $table->string('type')->nullable(true);
            $table->double('client_cost', 10, 2)->nullable(false);
            $table->double('supplier_cost', 10, 2)->nullable(false);
            $table->double('return_amount', 10, 2)->nullable(false);
            $table->double('rework_cost', 10, 2)->default(0.00)->nullable(true);
            $table->foreignId('step_id')->references('id')->on('steps')->nullable(false);
            $table->string('root_cause')->nullable(true);
            $table->string('lesson_learned')->nullable(true);
            $table->date('close_date')->nullable(false);
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateComplaints_cdtSet` BEFORE INSERT ON `complaints` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeComplaints_udtUpdate` BEFORE UPDATE ON `complaints` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('complaints');
    }
}

