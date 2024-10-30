<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalsTable extends Migration
{
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade'); // Mengaitkan dengan tabel payments
            $table->decimal('amount', 10, 2);
            $table->string('method'); // 'tunai' atau 'non_tunai'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawals');
    }
}
