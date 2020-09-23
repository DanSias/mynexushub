<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramEnterpriseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_enterprise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id');
            $table->string('program')->nullable();
            $table->integer('pod')->nullable();
            $table->string('college')->nullable();
            $table->string('quadrant')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('program_enterprise');
    }
}
