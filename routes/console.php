<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('oep:import-csv {path=storage/app/import/datos_oep.csv}', function (string $path) {
    $fullPath = str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $path)
        ? $path
        : base_path($path);

    if (! file_exists($fullPath)) {
        $this->error("Archivo no encontrado: {$fullPath}");
        return self::FAILURE;
    }

    $decode = static function (?string $value): string {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252');
    };

    $toInt = static function (?string $value): int {
        $decoded = trim((string) $value);
        if ($decoded === '') {
            return 0;
        }
        $decoded = preg_replace('/[^\d\-]/', '', $decoded);
        return (int) ($decoded ?: 0);
    };

    $csv = fopen($fullPath, 'rb');
    if (! $csv) {
        $this->error("No se pudo abrir: {$fullPath}");
        return self::FAILURE;
    }

    $headerRaw = fgetcsv($csv);
    if (! is_array($headerRaw)) {
        fclose($csv);
        $this->error('No se pudo leer cabecera CSV.');
        return self::FAILURE;
    }

    $headers = array_map($decode, $headerRaw);
    $index = [];
    foreach ($headers as $i => $name) {
        $index[$name] = $i;
    }

    $required = [
        'CodigoDepartamento', 'NombreDepartamento',
        'CodigoProvincia', 'NombreProvincia',
        'CodigoSeccion', 'NombreMunicipio',
        'CodigoLocalidad', 'NombreLocalidad',
        'CodigoRecinto', 'NombreRecinto',
        'NumeroMesa',
        'VotoValido', 'VotoBlanco', 'VotoNuloDirecto', 'VotoNuloDeclinacion', 'TotalVotoNulo', 'VotoEmitido', 'VotoValidoReal',
    ];

    foreach ($required as $col) {
        if (! array_key_exists($col, $index)) {
            fclose($csv);
            $this->error("Falta columna requerida: {$col}");
            return self::FAILURE;
        }
    }

    $orgBySigla = DB::table('org_politicas')->pluck('codigo_organizacion', 'sigla')->toArray();
    if (empty($orgBySigla)) {
        fclose($csv);
        $this->error('La tabla org_politicas está vacía. Carga primero las organizaciones.');
        return self::FAILURE;
    }

    $now = now();
    $rows = 0;
    $detalles = [];
    $seenDepartamento = [];
    $seenProvincia = [];
    $seenMunicipio = [];
    $seenLocalidad = [];
    $seenRecinto = [];
    $missingSiglas = [];
    $defaultHabilitaciones = [
        'Coripata' => ['JALLALLA LP', 'ASP', 'VENCEREMOS', 'UPC', 'IH', 'MTS', 'NGP', 'PATRIA-SOL'],
        'Guanay' => ['JALLALLA LP', 'MNR', 'UPC', 'LIBRE', 'IH', 'FRI', 'PDC', 'NGP', 'M.P.S.'],
        'Teoponte' => ['LIBRE', 'IH', 'VIDA', 'MTS', 'NGP', 'PATRIA-SOL', 'M.P.S.'],
    ];

    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    DB::table('municipio_org_habilitaciones')->truncate();
    DB::table('mesa_org_politica')->truncate();
    DB::table('mesas')->truncate();
    DB::table('recintos')->truncate();
    DB::table('localidades')->truncate();
    DB::table('municipios')->truncate();
    DB::table('provincias')->truncate();
    DB::table('departamentos')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    while (($row = fgetcsv($csv)) !== false) {
            if (! is_array($row) || count(array_filter($row, static fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $codigoDepartamento = $toInt($row[$index['CodigoDepartamento']] ?? '');
            $nombreDepartamento = $decode($row[$index['NombreDepartamento']] ?? '');
            $codigoProvincia = $toInt($row[$index['CodigoProvincia']] ?? '');
            $nombreProvincia = $decode($row[$index['NombreProvincia']] ?? '');
            $codigoSeccion = $toInt($row[$index['CodigoSeccion']] ?? '');
            $codigoMunicipio = (int) sprintf('%01d%02d%02d', $codigoDepartamento, $codigoProvincia, $codigoSeccion);
            $nombreMunicipio = $decode($row[$index['NombreMunicipio']] ?? '');
            $codigoLocalidad = $toInt($row[$index['CodigoLocalidad']] ?? '');
            $nombreLocalidad = $decode($row[$index['NombreLocalidad']] ?? '');
            $codigoRecintoBase = $toInt($row[$index['CodigoRecinto']] ?? '');
            $codigoRecinto = (int) sprintf('%05d%04d%05d', $codigoMunicipio, $codigoLocalidad, $codigoRecintoBase);
            $nombreRecinto = $decode($row[$index['NombreRecinto']] ?? '');
            $numeroMesa = $toInt($row[$index['NumeroMesa']] ?? '');

            if ($codigoDepartamento === 0 || $codigoProvincia === 0 || $codigoSeccion === 0 || $codigoMunicipio === 0 || $codigoLocalidad === 0 || $codigoRecintoBase === 0 || $codigoRecinto === 0 || $numeroMesa === 0) {
                continue;
            }

            if (! isset($seenDepartamento[$codigoDepartamento])) {
                DB::table('departamentos')->insert([
                    'codigo_departamento' => $codigoDepartamento,
                    'nombre_departamento' => $nombreDepartamento,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $seenDepartamento[$codigoDepartamento] = true;
            }

            if (! isset($seenProvincia[$codigoProvincia])) {
                DB::table('provincias')->insert([
                    'codigo_provincia' => $codigoProvincia,
                    'nombre_provincia' => $nombreProvincia,
                    'codigo_departamento' => $codigoDepartamento,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $seenProvincia[$codigoProvincia] = true;
            }

            if (! isset($seenMunicipio[$codigoMunicipio])) {
                DB::table('municipios')->insert([
                    'codigo_municipio' => $codigoMunicipio,
                    'nombre_municipio' => $nombreMunicipio,
                    'codigo_provincia' => $codigoProvincia,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $seenMunicipio[$codigoMunicipio] = true;
            }

            if (! isset($seenLocalidad[$codigoLocalidad])) {
                DB::table('localidades')->insert([
                    'codigo_localidad' => $codigoLocalidad,
                    'nombre_localidad' => $nombreLocalidad,
                    'codigo_municipio' => $codigoMunicipio,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $seenLocalidad[$codigoLocalidad] = true;
            }

            if (! isset($seenRecinto[$codigoRecinto])) {
                DB::table('recintos')->insert([
                    'codigo_recinto' => $codigoRecinto,
                    'nombre_recinto' => $nombreRecinto,
                    'codigo_localidad' => $codigoLocalidad,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $seenRecinto[$codigoRecinto] = true;
            }

            $codigoMesa = (int) sprintf('%014d%02d', $codigoRecinto, $numeroMesa);

            DB::table('mesas')->insert([
                'codigo_mesa' => $codigoMesa,
                'codigo_recinto' => $codigoRecinto,
                'numero_mesa' => $numeroMesa,
                'total_votos_validos' => $toInt($row[$index['VotoValido']] ?? ''),
                'votos_blancos' => $toInt($row[$index['VotoBlanco']] ?? ''),
                'votos_nulos' => $toInt($row[$index['TotalVotoNulo']] ?? ''),
                'total_votos_emitidos' => $toInt($row[$index['VotoEmitido']] ?? ''),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($orgBySigla as $sigla => $codigoOrganizacion) {
                if (! array_key_exists($sigla, $index)) {
                    $missingSiglas[$sigla] = true;
                    continue;
                }

                $votos = $toInt($row[$index[$sigla]] ?? '');
                if ($votos <= 0) {
                    continue;
                }

                $detalles[] = [
                    'codigo_mesa' => $codigoMesa,
                    'codigo_organizacion' => $codigoOrganizacion,
                    'registro_votos' => $votos,
                ];

                if (count($detalles) >= 1500) {
                    DB::table('mesa_org_politica')->insert($detalles);
                    $detalles = [];
                }
            }

            $rows++;
        }

    if (! empty($detalles)) {
        DB::table('mesa_org_politica')->insert($detalles);
        $detalles = [];
    }

    $habilitacionesRows = [];
    $nowH = now();
    foreach ($defaultHabilitaciones as $nombreMunicipio => $siglas) {
        $codigoMunicipio = DB::table('municipios')->where('nombre_municipio', $nombreMunicipio)->value('codigo_municipio');
        if (! $codigoMunicipio) {
            continue;
        }

        foreach ($siglas as $sigla) {
            $codigoOrg = DB::table('org_politicas')->where('sigla', $sigla)->value('codigo_organizacion');
            if (! $codigoOrg) {
                continue;
            }

            $habilitacionesRows[] = [
                'codigo_municipio' => (int) $codigoMunicipio,
                'codigo_organizacion' => (int) $codigoOrg,
                'created_at' => $nowH,
                'updated_at' => $nowH,
            ];
        }
    }

    if (! empty($habilitacionesRows)) {
        DB::table('municipio_org_habilitaciones')->insert($habilitacionesRows);
    }

    fclose($csv);

    $this->info("Importación completada. Filas procesadas: {$rows}");
    $this->line('Departamentos: '.DB::table('departamentos')->count());
    $this->line('Provincias: '.DB::table('provincias')->count());
    $this->line('Municipios: '.DB::table('municipios')->count());
    $this->line('Localidades: '.DB::table('localidades')->count());
    $this->line('Recintos: '.DB::table('recintos')->count());
    $this->line('Mesas: '.DB::table('mesas')->count());
    $this->line('Detalle votos: '.DB::table('mesa_org_politica')->count());
    $this->line('Habilitaciones Municipio-Org: '.DB::table('municipio_org_habilitaciones')->count());

    if (! empty($missingSiglas)) {
        $this->warn('Siglas no encontradas como columnas en CSV: '.implode(', ', array_keys($missingSiglas)));
    }

    return self::SUCCESS;
})->purpose('Importa estructura territorial y mesas desde CSV exportado de Excel OEP');
