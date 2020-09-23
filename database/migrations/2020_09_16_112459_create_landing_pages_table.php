<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandingPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable();
            $table->string('program');
            $table->string('channel')->nullable();
            $table->string('initiative')->nullable();
            $table->string('domain')->nullable();
            $table->string('slug')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('audience')->nullable();
            $table->string('left_form')->nullable();
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
        Schema::dropIfExists('landing_pages');
    }
}
