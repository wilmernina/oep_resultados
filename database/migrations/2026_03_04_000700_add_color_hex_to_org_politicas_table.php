<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('org_politicas', function (Blueprint $table) {
            $table->string('color_hex', 7)->nullable()->after('sigla');
        });
    }

    public function down(): void
    {
        Schema::table('org_politicas', function (Blueprint $table) {
            $table->dropColumn('color_hex');
        });
    }
};

