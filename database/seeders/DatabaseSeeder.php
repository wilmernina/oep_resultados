<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@oep.bo'],
            [
                'name' => 'Administrador SIREPRE',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'operador@oep.bo'],
            [
                'name' => 'Operador Mesa',
                'password' => Hash::make('operador12345'),
                'role' => 'operador',
            ]
        );

        User::updateOrCreate(
            ['email' => 'demo@oep.bo'],
            [
                'name' => 'Usuario Demo',
                'password' => Hash::make('demo12345'),
                'role' => 'operador',
            ]
        );

        DB::table('departamentos')->updateOrInsert(
            ['codigo_departamento' => 1],
            ['nombre_departamento' => 'La Paz', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('provincias')->updateOrInsert(
            ['codigo_provincia' => 101],
            ['nombre_provincia' => 'Murillo', 'codigo_departamento' => 1, 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('municipios')->updateOrInsert(
            ['codigo_municipio' => 10101],
            ['nombre_municipio' => 'La Paz', 'codigo_provincia' => 101, 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('localidades')->updateOrInsert(
            ['codigo_localidad' => 1010101],
            ['nombre_localidad' => 'Zona Central', 'codigo_municipio' => 10101, 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('recintos')->updateOrInsert(
            ['codigo_recinto' => 5001],
            ['nombre_recinto' => 'Unidad Educativa 1', 'codigo_localidad' => 1010101, 'created_at' => now(), 'updated_at' => now()]
        );

        foreach ([10001 => 1, 10002 => 2, 10003 => 3] as $codigoMesa => $numeroMesa) {
            DB::table('mesas')->updateOrInsert(
                ['codigo_mesa' => $codigoMesa],
                ['codigo_recinto' => 5001, 'numero_mesa' => $numeroMesa, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $organizaciones = [
            ['nombre_organizacion' => 'JUNTOS AL LLAMADO DE LOS PUEBLOS', 'sigla' => 'JALLALLA LP'],
            ['nombre_organizacion' => 'SUB CONSEJO TSIMANE REGIONAL LA PAZ', 'sigla' => 'SCTR-LP'],
            ['nombre_organizacion' => 'SOBERANÍA INDÍGENA CAMPESINA UNIDAD Y SOLIDARIA - JIWASAN ARUSA', 'sigla' => 'SICUS'],
            ['nombre_organizacion' => 'LEVANTAMIENTO DE UNIDAD SOCIAL 1° DE SEPTIEMBRE', 'sigla' => 'LUS-1S'],
            ['nombre_organizacion' => 'CABILDO DE AYLLUS ORIGINARIOS SAN ANDRES DE MACHACA - CAOSAM', 'sigla' => 'CAOSAM'],
            ['nombre_organizacion' => 'MARCA COLLOLO COPACABANA ANTAQUILLA - NACION PUKINA', 'sigla' => 'MCCA-NP'],
            ['nombre_organizacion' => 'ALIANZA SOCIAL PATRIOTICA', 'sigla' => 'ASP'],
            ['nombre_organizacion' => 'VENCEREMOS', 'sigla' => 'VENCEREMOS'],
            ['nombre_organizacion' => 'MOVIMIENTO NACIONALISTA REVOLUCIONARIO', 'sigla' => 'MNR'],
            ['nombre_organizacion' => 'SOMOS LA PAZ', 'sigla' => 'ASLP'],
            ['nombre_organizacion' => 'UNION POR EL CAMBIO', 'sigla' => 'UPC'],
            ['nombre_organizacion' => 'LIBERTAD Y REPÚBLICA', 'sigla' => 'LIBRE'],
            ['nombre_organizacion' => 'ALIANZA UNIDOS POR LOS PUEBLOS', 'sigla' => 'A-UPP'],
            ['nombre_organizacion' => 'MARKA DE AYLLUS COMUNIDADES ORIGINARIAS DE JESUS DE MACHACA', 'sigla' => 'MACOJMA'],
            ['nombre_organizacion' => 'INNOVACION HUMANA', 'sigla' => 'IH'],
            ['nombre_organizacion' => 'MOVIMIENTO KATARISTA PODER COMUNA', 'sigla' => 'MK-PC'],
            ['nombre_organizacion' => 'VAMOS INTEGRANDO EL DESARROLLO AUTONOMICO', 'sigla' => 'VIDA'],
            ['nombre_organizacion' => 'FRENTE REVOLUCIONARIO DE IZQUIERDA', 'sigla' => 'FRI'],
            ['nombre_organizacion' => 'PARTIDO DEMOCRATA CRISTIANO', 'sigla' => 'PDC'],
            ['nombre_organizacion' => 'FRENTE DE UNIDAD NACIONAL', 'sigla' => 'UN'],
            ['nombre_organizacion' => 'ACCION SOCIAL DE INTEGRACION', 'sigla' => 'ASI'],
            ['nombre_organizacion' => 'CONSEJO DE AYLLUS TARAKU MARCA', 'sigla' => 'CAOTM'],
            ['nombre_organizacion' => 'MOVIMIENTO TERCER SISTEMA', 'sigla' => 'MTS'],
            ['nombre_organizacion' => 'TAYKAMARKA ACHIRI AXAWIRI - MARKANAKAS LAYKU', 'sigla' => 'ML'],
            ['nombre_organizacion' => 'NUEVA GENERACIÓN PATRIÓTICA', 'sigla' => 'NGP'],
            ['nombre_organizacion' => 'ALIANZA PATRIA SOL', 'sigla' => 'PATRIA-SOL'],
            ['nombre_organizacion' => 'MOVIMIENTO POR LA SOBERANIA', 'sigla' => 'M.P.S.'],
            ['nombre_organizacion' => 'TAQUINI SARTASIÑANI', 'sigla' => 'T.S'],
            ['nombre_organizacion' => 'AUTONOMÍA PARA BOLIVIA SÚMATE', 'sigla' => 'APB-SUMATE'],
            ['nombre_organizacion' => 'ALIANZA PATRIA LA PAZ', 'sigla' => 'PATRIA-LA-PAZ'],
            ['nombre_organizacion' => 'POR UN MUNICIPIO ALTERNATIVO', 'sigla' => 'PUMA'],
            ['nombre_organizacion' => 'PODER DE LOS PUEBLOS ORIGINARIOS', 'sigla' => 'PODER-O'],
            ['nombre_organizacion' => 'SUMA POR EL BIEN COMÚN', 'sigla' => 'SPBC'],
        ];

        foreach ($organizaciones as $indice => $organizacion) {
            DB::table('org_politicas')->updateOrInsert(
                ['codigo_organizacion' => $indice + 1],
                [
                    'nombre_organizacion' => $organizacion['nombre_organizacion'],
                    'sigla' => $organizacion['sigla'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
