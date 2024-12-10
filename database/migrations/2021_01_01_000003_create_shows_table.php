<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shows', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->integer('duration')->nullable();
            $table->string('age')->nullable();
            $table->json('cast')->nullable();
            $table->date('premiera')->nullable();
            $table->json('description')->nullable();
            $table->unsignedBigInteger('primary_media_id')->nullable();
            $table->foreign('primary_media_id')->references('id')->on('spacemedia')
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('primary_media_ext',4)->nullable();
            $table->unsignedBigInteger('genre_id')->nullable();
            $table->foreign('genre_id')->references('id')->on('genres')
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->integer('sort_order')->default(1);
            $table->boolean('active')->default(1)->index();
            $table->string('slug',200)->nullable()->index()->unique();
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
        Schema::dropIfExists('shows');
    }
}
