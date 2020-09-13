<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('school')->nullable();
            $table->string('type')->nullable();
            $table->string('url')->nullable();
            $table->string('facebook')->nullable();
            $table->string('state')->nullable();
            $table->integer('zip')->nullable();
            $table->string('region')->nullable();
            $table->float('latitude', 7, 4)->nullable();
            $table->float('longitude', 7, 4)->nullable();
            $table->string('time_zone')->nullable();
            $table->integer('school_id')->nullable();
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
        Schema::dropIfExists('partners');
    }
}
