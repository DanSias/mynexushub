<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeo2LinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seo2_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id');
            $table->string('program');
            $table->string('ap_url');
            $table->string('ap_title');
            $table->string('href');
            $table->string('anchor');
            $table->string('confirmed');
            $table->string('type');
            $table->text('notes');
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
        Schema::dropIfExists('seo2_links');
    }
}
