<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_departamento')->primary();
            $table->string('nombre_departamento', 100);
            $table->timestamps();
        });

        Schema::create('provincias', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_provincia')->primary();
            $table->string('nombre_provincia', 100);
            $table->unsignedBigInteger('codigo_departamento');
            $table->timestamps();

            $table->foreign('codigo_departamento')->references('codigo_departamento')->on('departamentos');
        });

        Schema::create('municipios', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_municipio')->primary();
            $table->string('nombre_municipio', 120);
            $table->unsignedBigInteger('codigo_provincia');
            $table->timestamps();

            $table->foreign('codigo_provincia')->references('codigo_provincia')->on('provincias');
        });

        Schema::create('localidades', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_localidad')->primary();
            $table->string('nombre_localidad', 120);
            $table->unsignedBigInteger('codigo_municipio');
            $table->timestamps();

            $table->foreign('codigo_municipio')->references('codigo_municipio')->on('municipios');
        });

        Schema::create('recintos', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_recinto')->primary();
            $table->string('nombre_recinto', 150);
            $table->unsignedBigInteger('codigo_localidad');
            $table->timestamps();

            $table->foreign('codigo_localidad')->references('codigo_localidad')->on('localidades');
        });

        Schema::create('org_politicas', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_organizacion')->primary();
            $table->string('nombre_organizacion', 150);
            $table->string('sigla', 30)->unique();
            $table->timestamps();
        });

        Schema::create('mesas', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_mesa')->primary();
            $table->unsignedBigInteger('codigo_recinto');
            $table->unsignedInteger('numero_mesa');
            $table->timestamps();

            $table->foreign('codigo_recinto')->references('codigo_recinto')->on('recintos');
            $table->unique(['codigo_recinto', 'numero_mesa'], 'uq_recinto_numero_mesa');
        });

        Schema::create('mesa_org_politica', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_mesa');
            $table->unsignedBigInteger('codigo_organizacion');
            $table->unsignedInteger('registro_votos')->default(0);

            $table->foreign('codigo_mesa')->references('codigo_mesa')->on('mesas')->onDelete('cascade');
            $table->foreign('codigo_organizacion')->references('codigo_organizacion')->on('org_politicas');
            $table->primary(['codigo_mesa', 'codigo_organizacion'], 'pk_mesa_org_politica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesa_org_politica');
        Schema::dropIfExists('mesas');
        Schema::dropIfExists('org_politicas');
        Schema::dropIfExists('recintos');
        Schema::dropIfExists('localidades');
        Schema::dropIfExists('municipios');
        Schema::dropIfExists('provincias');
        Schema::dropIfExists('departamentos');
    }
};
