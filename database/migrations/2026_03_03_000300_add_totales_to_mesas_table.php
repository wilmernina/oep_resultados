<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->unsignedInteger('total_votos_validos')->default(0)->after('numero_mesa');
            $table->unsignedInteger('votos_blancos')->default(0)->after('total_votos_validos');
            $table->unsignedInteger('votos_nulos')->default(0)->after('votos_blancos');
            $table->unsignedInteger('total_votos_emitidos')->default(0)->after('votos_nulos');
        });
    }

    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropColumn([
                'total_votos_validos',
                'votos_blancos',
                'votos_nulos',
                'total_votos_emitidos',
            ]);
        });
    }
};

