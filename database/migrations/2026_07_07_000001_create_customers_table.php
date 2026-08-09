<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_import_id')->constrained()->restrictOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique();
            $table->string('phone_number', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('country', 100);
            $table->timestamps();

            $table->index(['last_name', 'first_name']);
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
