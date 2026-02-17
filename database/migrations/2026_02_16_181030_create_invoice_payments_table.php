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
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
                        $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('payment_id');

            $table->decimal('invoice_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->decimal('rest_amount', 10, 2);

            $table->string('invoice_no');
            $table->timestamps();

            $table->foreign('invoice_id')
                  ->references('id')
                  ->on('invoices')
                  ->onDelete('cascade');

            $table->foreign('payment_id')
                  ->references('id')
                  ->on('payments')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
