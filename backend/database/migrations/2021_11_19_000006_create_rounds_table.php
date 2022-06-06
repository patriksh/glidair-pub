<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rounds', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participant_id');
            $table->integer('round');
            $table->integer('score');
            $table->boolean('ignore')->default(false);
            $table->timestamps();

            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
        });
    }
}
