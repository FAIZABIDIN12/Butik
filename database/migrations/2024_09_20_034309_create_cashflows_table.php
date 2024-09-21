<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashflows', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // Kolom untuk tanggal
            $table->string('description');
            $table->enum('transaction_type', ['in', 'out']);
            $table->decimal('amount', 10, 2);
            $table->decimal('current_balance', 10, 2)->nullable(); // Atur sesuai kebutuhan
            $table->string('category_code'); // Kolom untuk kode kategori
            $table->timestamps();
        
            // Foreign key untuk category_code
            $table->foreign('category_code')
                  ->references('code')->on('categories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cashflows');
    }
}
