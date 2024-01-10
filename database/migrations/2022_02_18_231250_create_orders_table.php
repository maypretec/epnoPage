<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->date('cot_date')->nullable(false);
            $table->date('po_date')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('client_po_file')->nullable();
            $table->string('order_num')->nullable(false);
            $table->string('concept_order')->nullable(false);
            $table->double('iva', 10, 2)->nullable();
            $table->double('total_client', 10, 2)->nullable();
            $table->double('subtotal_client', 10, 2)->nullable();
            $table->date('expiration_date')->nullable(false);
            $table->integer('expiration_days')->nullable(false);
            $table->boolean('status')->default(true)->nullable(false);
            $table->double('iva_supplier', 10, 2)->nullable();
            $table->double('total_supplier', 10, 2)->nullable();
            $table->double('subtotal_supplier', 10, 2)->nullable();
            $table->double('return_amount', 10, 2)->nullable();
            $table->double('vendor_commission', 10, 2)->nullable();
            $table->double('capital_commission', 10, 2)->nullable();
            $table->double('net_utility', 10, 2)->nullable();
            $table->string('investor')->nullable();
            $table->foreignId('buyer')->references('id')->on('users')->nullable('false');
            $table->json('supplier')->nullable();
            $table->date('expiration_date_supplier')->nullable(false);
            $table->boolean('supplier_status')->default(true)->nullable(false);
            $table->string('invoice_file')->nullable();
            $table->boolean('is_po')->default(true)->nullable(false);
            $table->foreignId('service_type_id')->references('id')->on('service_types')->nullable('false');
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateOrder_cdtSet` BEFORE INSERT ON `orders` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeOrder_udtUpdate` BEFORE UPDATE ON `orders` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
