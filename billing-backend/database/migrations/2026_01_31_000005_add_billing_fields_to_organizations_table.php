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
        Schema::table('organizations', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(12.00)->after('email');
            $table->string('currency', 3)->default('PHP')->after('tax_rate');
            $table->integer('payment_terms')->default(30)->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'currency', 'payment_terms']);
        });
    }
};