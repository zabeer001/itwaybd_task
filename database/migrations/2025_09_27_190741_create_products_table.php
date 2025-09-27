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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // id bigint PK auto-increment
            $table->string('name')->index(); // Product name
            $table->decimal('price', 10, 2); // Base price
            $table->text('description')->nullable(); // Optional description
            $table->timestamps(); // created_at & updated_at

            $table->integer('softdelete')->default(0)->index(); // Soft delete column with index
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
