<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateValuestreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('valuestreams', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->boolean('status')->default(true)->nullable(false);
            $table->timestamps();
        });

        DB::unprepared('CREATE TRIGGER `onCreateValuestream_cdtSet` BEFORE INSERT ON `valuestreams` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
        DB::unprepared('CREATE TRIGGER `onChangeValuestream_udtUpdate` BEFORE UPDATE ON `valuestreams` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('valuestreams');
    }
}
