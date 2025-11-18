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
        Schema::table('user_locations', function (Blueprint $table) {
            $table->string('street_name')->nullable()->after('city');
            $table->string('street_number')->nullable()->after('street_name');
            $table->string('district')->nullable()->after('street_number');
            $table->string('postal_code')->nullable()->after('district');
            $table->text('formatted_address')->nullable()->after('postal_code');
            $table->enum('location_type', ['ip', 'gps'])->default('ip')->after('formatted_address');
            $table->decimal('accuracy', 10, 2)->nullable()->after('location_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_locations', function (Blueprint $table) {
            $table->dropColumn([
                'street_name', 'street_number', 'district', 'postal_code',
                'formatted_address', 'location_type', 'accuracy'
            ]);
        });
    }
};
