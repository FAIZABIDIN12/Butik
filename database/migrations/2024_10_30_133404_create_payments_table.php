<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('penjualan_id'); // Ubah ke unsignedInteger untuk sesuai dengan id_penjualan
            $table->decimal('amount', 15, 2); // Menyimpan jumlah pembayaran
            $table->enum('metode_pembayaran', ['tunai', 'non_tunai']); // Metode pembayaran
            $table->timestamps();

            // Menambahkan foreign key constraint
            $table->foreign('penjualan_id')->references('id_penjualan')->on('penjualan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
