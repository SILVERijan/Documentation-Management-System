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
        Schema::table('document_sections', function (Blueprint $table) {
            $table->integer('level')->default(2)->after('content');
            $table->foreignId('parent_id')->nullable()->after('level')->constrained('document_sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_sections', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['level', 'parent_id']);
        });
    }
};
