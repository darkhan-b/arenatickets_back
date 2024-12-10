<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->boolean('actor')->default(0)->index();
            $table->boolean('manager')->default(0)->index();
            $table->boolean('tech')->default(0)->index();
            $table->boolean('admin')->default(0)->index();
            $table->json('description')->nullable();
            $table->unsignedBigInteger('primary_media_id')->nullable();
            $table->foreign('primary_media_id')->references('id')->on('spacemedia')
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('primary_media_ext',4)->nullable();
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
        Schema::dropIfExists('staff');
    }
}
