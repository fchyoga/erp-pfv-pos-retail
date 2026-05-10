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
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable()->after('discount');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable()->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_type']);
        });
    }
};
