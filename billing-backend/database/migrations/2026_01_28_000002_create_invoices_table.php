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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('number')->unique();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes as required: (organization_id, vendor_id) on invoices
            $table->index(['organization_id', 'vendor_id']);
            $table->index('organization_id');
            $table->index('vendor_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
