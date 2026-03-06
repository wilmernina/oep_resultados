<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_municipio')->nullable()->after('role');
            $table->foreign('codigo_municipio')->references('codigo_municipio')->on('municipios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['codigo_municipio']);
            $table->dropColumn('codigo_municipio');
        });
    }
};

