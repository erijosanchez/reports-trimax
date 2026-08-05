<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('surveys', 'sede_id')) {
            Schema::table('surveys', function (Blueprint $table) {
                $table->unsignedBigInteger('sede_id')->nullable()->after('user_id');
                $table->foreign('sede_id')->references('id')->on('users_marketing')->onDelete('set null');
                $table->index('sede_id', 'idx_surveys_sede_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('surveys', 'sede_id')) {
            Schema::table('surveys', function (Blueprint $table) {
                $table->dropForeign(['sede_id']);
                $table->dropIndex('idx_surveys_sede_id');
                $table->dropColumn('sede_id');
            });
        }
    }
};
