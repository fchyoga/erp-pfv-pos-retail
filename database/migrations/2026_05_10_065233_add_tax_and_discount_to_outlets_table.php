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
        Schema::table('outlets', function (Blueprint $table) {
            $table->decimal('tax_percentage', 5, 2)->default(11); // 11% default tax
            $table->decimal('discount_percentage', 5, 2)->default(0); // 0% default discount
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['tax_percentage', 'discount_percentage']);
        });
    }
};
