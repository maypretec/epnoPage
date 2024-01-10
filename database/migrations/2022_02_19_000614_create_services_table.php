<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->references('id')->on('orders')->unique()->nullable('false');
            $table->string('order_num')->nullable(false);
            $table->string('title')->nullable(false);
            $table->text('description')->nullable(false);
            $table->foreignId('user_id')->references('id')->on('users')->nullable('false');
            $table->foreignId('step_id')->references('id')->on('steps')->nullable('false');
            $table->double('client_cost', 10, 2)->nullable();
            $table->double('supplier_cost', 10, 2)->nullable();
            $table->string('quote_file')->nullable();
            $table->date('client_deadline')->nullable();
            $table->string('type')->nullable(false);
            $table->string('prioridad')->nullable(true);
            $table->integer('rev')->default(0)->nullable(false);
            $table->boolean('status')->default(true)->nulleable(false);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateService_cdtSet` BEFORE INSERT ON `services` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeService_udtUpdate` BEFORE UPDATE ON `services` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
