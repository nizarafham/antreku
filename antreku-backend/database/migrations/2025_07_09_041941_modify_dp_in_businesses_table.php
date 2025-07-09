<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Hapus kolom lama jika ada
            if (Schema::hasColumn('businesses', 'dp_amount')) {
                $table->dropColumn('dp_amount');
            }
            // Tambah kolom baru setelah 'slug'
            $table->tinyInteger('dp_percentage')->unsigned()->default(0)->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('dp_percentage');
            $table->decimal('dp_amount', 10, 2)->default(0);
        });
    }
};
