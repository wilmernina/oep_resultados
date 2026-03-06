<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipio_org_habilitaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_municipio');
            $table->unsignedBigInteger('codigo_organizacion');
            $table->timestamps();

            $table->primary(['codigo_municipio', 'codigo_organizacion'], 'pk_municipio_org_habilitaciones');
            $table->foreign('codigo_municipio')->references('codigo_municipio')->on('municipios')->onDelete('cascade');
            $table->foreign('codigo_organizacion')->references('codigo_organizacion')->on('org_politicas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipio_org_habilitaciones');
    }
};

