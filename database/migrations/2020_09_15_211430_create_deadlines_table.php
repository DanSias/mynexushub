<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeadlinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deadlines', function (Blueprint $table) {
            $table->id();
            $table->string('program');
            $table->integer('year');
            $table->string('term');
            $table->date('app')->nullable();
            $table->date('cf')->nullable();
            $table->date('start')->nullable();
            $table->date('reg')->nullable();
            $table->date('drop')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deadlines');
    }
}
