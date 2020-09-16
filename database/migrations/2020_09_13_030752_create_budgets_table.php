<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id');
            $table->foreignId('user_id');
            $table->integer('year');
            $table->string('scenario');
            $table->string('status')->nullable();
            $table->string('metric');
            $table->string('channel');
            $table->string('initiative');
            $table->integer('january');
            $table->integer('february');
            $table->integer('march');
            $table->integer('april');
            $table->integer('may');
            $table->integer('june');
            $table->integer('july');
            $table->integer('august');
            $table->integer('september');
            $table->integer('october');
            $table->integer('november');
            $table->integer('december');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('budgets');
    }
}
