<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->string('code')->primary(); 
            $table->enum('type', ['in', 'out', 'mutation']); 
            $table->string('name'); 
            $table->string('debit_account_code')->nullable(); 
            $table->string('credit_account_code')->nullable(); 
            $table->text('note')->nullable(); 
            $table->timestamps(); 

            // Foreign key untuk debit_account_code
            $table->foreign('debit_account_code')
                  ->references('code')->on('accounts')
                  ->onDelete('set null');

            // Foreign key untuk credit_account_code
            $table->foreign('credit_account_code')
                  ->references('code')->on('accounts')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
