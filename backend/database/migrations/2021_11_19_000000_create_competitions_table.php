<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitions', function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->integer('rounds');
            $table->integer('rounds_ignored')->default(0);
            $table->string('logo')->nullable();
            $table->string('director')->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }
}
