<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('program_id');
            $table->string('channel');
            $table->string('type');
            $table->text('description');
            $table->string('status');
            $table->integer('spend');
            $table->integer('leads');
            $table->boolean('leads_incremental');
            $table->string('timeframe');
            $table->dateTime('revised_at');
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
        Schema::dropIfExists('business_cases');
    }
}
