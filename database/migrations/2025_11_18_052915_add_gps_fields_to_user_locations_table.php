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
        if (!Schema::hasTable('user_locations')) {
            return;
        }

        Schema::table('user_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('user_locations', 'street_name')) {
                $table->string('street_name')->nullable()->after('city');
            }
            if (!Schema::hasColumn('user_locations', 'street_number')) {
                $table->string('street_number')->nullable()->after('street_name');
            }
            if (!Schema::hasColumn('user_locations', 'district')) {
                $table->string('district')->nullable()->after('street_number');
            }
            if (!Schema::hasColumn('user_locations', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('district');
            }
            if (!Schema::hasColumn('user_locations', 'formatted_address')) {
                $table->text('formatted_address')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('user_locations', 'location_type')) {
                $table->enum('location_type', ['ip', 'gps'])->default('ip')->after('formatted_address');
            }
            if (!Schema::hasColumn('user_locations', 'accuracy')) {
                $table->decimal('accuracy', 10, 2)->nullable()->after('location_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('user_locations')) {
            return;
        }

        Schema::table('user_locations', function (Blueprint $table) {
            foreach (['street_name', 'street_number', 'district', 'postal_code', 'formatted_address', 'location_type', 'accuracy'] as $col) {
                if (Schema::hasColumn('user_locations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
