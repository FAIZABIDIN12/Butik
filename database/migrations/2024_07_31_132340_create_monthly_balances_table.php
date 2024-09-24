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
        Schema::create('monthly_balances', function (Blueprint $table) {
            $table->id();
            $table->string('account_code');
            $table->string('month');
            $table->decimal('balance', 15, 2);
            $table->timestamps();
            $table->foreign('account_code')
                ->references('code')
                ->on('accounts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_balances', function (Blueprint $table) {
            $table->dropForeign(['account_code']);
        });
        Schema::dropIfExists('monthly_balances');
    }
};
