<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id');
            $table->string('code');
            $table->string('active')->nullable();
            $table->string('name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('strategy')->nullable();
            $table->float('ltv', 8, 2)->nullable();
            $table->string('location')->nullable();
            $table->integer('bu')->nullable();
            $table->string('vertical')->nullable();
            $table->string('subvertical')->nullable();
            $table->string('level')->nullable();
            $table->string('type')->nullable();
            $table->boolean('priority')->default(false);
            $table->string('accreditation')->nullable();
            $table->string('online_percent')->nullable();
            $table->integer('start_year')->nullable();
            $table->integer('start_month')->nullable();
            $table->integer('entry_points')->nullable();
            $table->date('renewal_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('contacts')->nullable();
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
        Schema::dropIfExists('programs');
    }
}
