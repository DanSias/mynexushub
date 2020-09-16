<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable();
            $table->string('code')->nullable();
            $table->integer('tuition_in_state')->nullable();
            $table->integer('tuition_out_state')->nullable();
            $table->text('tuition_notes')->nullable();
            $table->text('fees')->nullable();
            $table->float('gpa_required', 5, 2)->nullable();
            $table->text('gpa_notes')->nullable();
            $table->string('testing_required')->nullable();
            $table->text('testing_notes')->nullable();
            $table->integer('tuition_credit_hour')->nullable();
            $table->integer('tuition_credit_hour_os')->nullable();
            $table->integer('credit_hours')->nullable();
            $table->integer('credit_hours_max')->nullable();
            $table->integer('program_months')->nullable();
            $table->integer('program_months_max')->nullable();
            $table->string('gmat')->nullable();
            $table->string('gmat_waiver')->nullable();
            $table->string('gre')->nullable();
            $table->string('gre_waiver')->nullable();
            $table->text('experience_needed')->nullable();
            $table->string('transfer_credits_accepted')->nullable();
            $table->text('transfer_credits_details')->nullable();
            $table->text('program_notes')->nullable();
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
        Schema::dropIfExists('program_requirements');
    }
}
