<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreateProductCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('epno_part_id')->references('id')->on('epno_parts')->nullable(false);
            $table->foreignId('user_comment')->references('id')->on('users')->nullable(false);
            $table->string('comment')->nullable(false);
            $table->foreignId('user_answer')->references('id')->on('users')->nullable(true);
            $table->string('answer')->nullable(true);
            $table->timestamps();
        });

         // Triggers
         DB::unprepared('CREATE TRIGGER `onCreateProductComments_cdtSet` BEFORE INSERT ON `product_comments` FOR EACH ROW SET new.created_at = CURRENT_TIMESTAMP, new.updated_at = CURRENT_TIMESTAMP');
         DB::unprepared('CREATE TRIGGER `onChangeProductComments_udtUpdate` BEFORE UPDATE ON `product_comments` FOR EACH ROW SET new.updated_at = CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_comments');
    }
}
