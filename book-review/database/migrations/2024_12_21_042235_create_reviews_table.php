<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // if (!Schema::hasTable('reviews')) {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // $table->unsignedBigInteger('book_id');

            $table->text('review');
            $table->unsignedTinyInteger('rating');

            $table->timestamps();

             //$table->foreign('book_id')->references('id')->on('books') //telling the foreign key (book_id) references which column (id) on what table (books)
             //    ->onDelete('cascade'); //when a book is deleted, all reviews will be removed as well
            $table->foreignId('book_id')->constrained() //this shorthand replaces line 17 and 24-25
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
