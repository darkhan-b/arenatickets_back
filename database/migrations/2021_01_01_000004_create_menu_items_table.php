<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('url')->nullable();
            $table->integer('sort_order')->default(1);
            $table->unsignedBigInteger('parent_menu_item_id')->nullable();
            $table->foreign('parent_menu_item_id')->references('id')->on('menu_items')
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->boolean('header')->default(false)->index();
            $table->boolean('footer')->default(false)->index();
            $table->boolean('active')->default(1)->index();
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
        Schema::dropIfExists('menu_items');
    }
}
