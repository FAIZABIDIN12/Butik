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
        Schema::create('categories', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->enum('type', ['in', 'out', 'mutation']);
            $table->string('name');
            $table->string('debit_account_code')->nullable();
            $table->string('credit_account_code')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('debit_account_code')->references('code')->on('accounts')->onDelete('set null');
            $table->foreign('credit_account_code')->references('code')->on('accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
