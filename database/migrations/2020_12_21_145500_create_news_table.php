<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('title')->nullable();
            $table->json('teaser')->nullable();
            $table->json('description')->nullable();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('primary_media_id')->nullable();
            $table->foreign('primary_media_id')->references('id')->on('spacemedia')
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('primary_media_ext',4)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->string('slug',200)->nullable()->index()->unique();
            $table->boolean('active')->default(1)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('news');
    }
}
