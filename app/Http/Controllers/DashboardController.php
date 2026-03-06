<?php

namespace App\Http\Controllers;

use App\Models\Localidad;
use App\Models\Mesa;
use App\Models\MesaOrgPolitica;
use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Recinto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $municipioOperador = ($usuario?->role === 'operador') ? (int) ($usuario->codigo_municipio ?? 0) : null;

        if ($usuario?->role === 'operador' && $municipioOperador === 0) {
            return view('dashboard', [
                'resumen' => [
                    'validos' => 0,
                    'blancos' => 0,
                    'nulos' => 0,
                    'total_mesas' => 0,
                    'actas_computadas' => 0,
                    'pendientes_mesas' => 0,
                    'pct_actas' => 0,
                ],
                'barras' => [
                    'labels' => [],
                    'data' => [],
                ],
                'filtros' => [
                    'codigo_provincia' => null,
                    'codigo_municipio' => null,
                    'codigo_localidad' => null,
                    'codigo_recinto' => null,
                ],
                'opciones' => [
                    'provincias' => collect(),
                    'municipios' => collect(),
                    'localidades' => collect(),
                    'recintos' => collect(),
                ],
                'catalogos' => [
                    'municipios' => collect(),
                    'localidades' => collect(),
                    'recintos' => collect(),
                ],
            ]);
        }

        $filtros = [
            'codigo_provincia' => $request->integer('codigo_provincia') ?: null,
            'codigo_municipio' => $request->integer('codigo_municipio') ?: null,
            'codigo_localidad' => $request->integer('codigo_localidad') ?: null,
            'codigo_recinto' => $request->integer('codigo_recinto') ?: null,
        ];

        if ($municipioOperador) {
            $filtros['codigo_municipio'] = $municipioOperador;
        }

        $baseMesas = Mesa::query()
            ->join('recintos', 'recintos.codigo_recinto', '=', 'mesas.codigo_recinto')
            ->join('localidades', 'localidades.codigo_localidad', '=', 'recintos.codigo_localidad')
            ->join('municipios', 'municipios.codigo_municipio', '=', 'localidades.codigo_municipio')
            ->join('provincias', 'provincias.codigo_provincia', '=', 'municipios.codigo_provincia');

        $this->aplicarFiltrosUbicacion($baseMesas, $filtros);

        $totales = (clone $baseMesas)
            ->whereExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('mesa_org_politica')
                    ->whereColumn('mesa_org_politica.codigo_mesa', 'mesas.codigo_mesa');
            })
            ->selectRaw('
                COALESCE(SUM(mesas.total_votos_validos), 0) as validos,
                COALESCE(SUM(mesas.votos_blancos), 0) as blancos,
                COALESCE(SUM(mesas.votos_nulos), 0) as nulos
            ')
            ->first();

        $totalMesas = (clone $baseMesas)->count('mesas.codigo_mesa');

        $actasComputadas = (clone $baseMesas)
            ->whereExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('mesa_org_politica')
                    ->whereColumn('mesa_org_politica.codigo_mesa', 'mesas.codigo_mesa')
                    ->where('mesa_org_politica.registro_votos', '>', 0);
            })
            ->count('mesas.codigo_mesa');

        $validos = (int) ($totales->validos ?? 0);
        $blancos = (int) ($totales->blancos ?? 0);
        $nulos = (int) ($totales->nulos ?? 0);
        $totalMesas = (int) $totalMesas;
        $actasComputadas = (int) $actasComputadas;
        $pendientesMesas = max($totalMesas - $actasComputadas, 0);
        $pctActas = $totalMesas > 0 ? round(($actasComputadas / $totalMesas) * 100, 2) : 0.0;

        $porPartido = MesaOrgPolitica::query()
            ->join('org_politicas', 'org_politicas.codigo_organizacion', '=', 'mesa_org_politica.codigo_organizacion')
            ->join('mesas', 'mesas.codigo_mesa', '=', 'mesa_org_politica.codigo_mesa')
            ->join('recintos', 'recintos.codigo_recinto', '=', 'mesas.codigo_recinto')
            ->join('localidades', 'localidades.codigo_localidad', '=', 'recintos.codigo_localidad')
            ->join('municipios', 'municipios.codigo_municipio', '=', 'localidades.codigo_municipio')
            ->join('provincias', 'provincias.codigo_provincia', '=', 'municipios.codigo_provincia');
        $this->aplicarFiltrosUbicacion($porPartido, $filtros);
        $porPartido = $porPartido
            ->selectRaw('org_politicas.sigla, org_politicas.color_hex, SUM(mesa_org_politica.registro_votos) as total')
            ->groupBy('org_politicas.sigla', 'org_politicas.color_hex')
            ->orderByDesc('total')
            ->get();

        $provincias = Provincia::query()
            ->when($municipioOperador, function ($q) use ($municipioOperador): void {
                $q->whereExists(function ($sub) use ($municipioOperador): void {
                    $sub->select(DB::raw(1))
                        ->from('municipios')
                        ->whereColumn('municipios.codigo_provincia', 'provincias.codigo_provincia')
                        ->where('municipios.codigo_municipio', $municipioOperador);
                });
            })
            ->orderBy('nombre_provincia')
            ->get(['codigo_provincia', 'nombre_provincia']);

        $municipios = Municipio::query()
            ->when($filtros['codigo_provincia'], function ($q) use ($filtros): void {
                $q->where('codigo_provincia', $filtros['codigo_provincia']);
            })
            ->when($municipioOperador, function ($q) use ($municipioOperador): void {
                $q->where('codigo_municipio', $municipioOperador);
            })
            ->orderBy('nombre_municipio')
            ->get(['codigo_municipio', 'nombre_municipio']);
        $localidades = $filtros['codigo_municipio']
            ? Localidad::where('codigo_municipio', $filtros['codigo_municipio'])->orderBy('nombre_localidad')->get(['codigo_localidad', 'nombre_localidad'])
            : collect();
        $recintos = $filtros['codigo_localidad']
            ? Recinto::where('codigo_localidad', $filtros['codigo_localidad'])->orderBy('nombre_recinto')->get(['codigo_recinto', 'nombre_recinto'])
            : collect();
        $allMunicipios = Municipio::query()
            ->when($municipioOperador, function ($q) use ($municipioOperador): void {
                $q->where('codigo_municipio', $municipioOperador);
            })
            ->orderBy('nombre_municipio')
            ->get(['codigo_municipio', 'nombre_municipio', 'codigo_provincia']);
        $allLocalidades = Localidad::orderBy('nombre_localidad')->get(['codigo_localidad', 'nombre_localidad', 'codigo_municipio']);
        $allRecintos = Recinto::orderBy('nombre_recinto')->get(['codigo_recinto', 'nombre_recinto', 'codigo_localidad']);

        return view('dashboard', [
            'resumen' => [
                'validos' => $validos,
                'blancos' => $blancos,
                'nulos' => $nulos,
                'total_mesas' => $totalMesas,
                'actas_computadas' => $actasComputadas,
                'pendientes_mesas' => $pendientesMesas,
                'pct_actas' => $pctActas,
            ],
            'barras' => [
                'labels' => $porPartido->pluck('sigla'),
                'data' => $porPartido->pluck('total'),
                'colors' => $porPartido->pluck('color_hex'),
            ],
            'filtros' => $filtros,
            'opciones' => [
                'provincias' => $provincias,
                'municipios' => $municipios,
                'localidades' => $localidades,
                'recintos' => $recintos,
            ],
            'catalogos' => [
                'municipios' => $allMunicipios,
                'localidades' => $allLocalidades,
                'recintos' => $allRecintos,
            ],
        ]);
    }

    public function municipiosPorProvincia(int $codigoProvincia): JsonResponse
    {
        $usuario = request()->user();
        $data = Municipio::where('codigo_provincia', $codigoProvincia)
            ->when($usuario?->role === 'operador' && $usuario?->codigo_municipio, function ($q) use ($usuario): void {
                $q->where('codigo_municipio', $usuario->codigo_municipio);
            })
            ->orderBy('nombre_municipio')
            ->get(['codigo_municipio', 'nombre_municipio']);

        return response()->json($data);
    }

    public function localidadesPorMunicipio(int $codigoMunicipio): JsonResponse
    {
        $usuario = request()->user();
        if ($usuario?->role === 'operador' && $usuario?->codigo_municipio && (int) $usuario->codigo_municipio !== (int) $codigoMunicipio) {
            return response()->json([]);
        }

        $data = Localidad::where('codigo_municipio', $codigoMunicipio)
            ->orderBy('nombre_localidad')
            ->get(['codigo_localidad', 'nombre_localidad']);

        return response()->json($data);
    }

    public function recintosPorLocalidad(int $codigoLocalidad): JsonResponse
    {
        $usuario = request()->user();
        if ($usuario?->role === 'operador' && $usuario?->codigo_municipio) {
            $ok = Localidad::where('codigo_localidad', $codigoLocalidad)
                ->where('codigo_municipio', $usuario->codigo_municipio)
                ->exists();
            if (! $ok) {
                return response()->json([]);
            }
        }

        $data = Recinto::where('codigo_localidad', $codigoLocalidad)
            ->orderBy('nombre_recinto')
            ->get(['codigo_recinto', 'nombre_recinto']);

        return response()->json($data);
    }

    private function aplicarFiltrosUbicacion($query, array $filtros): void
    {
        if (! empty($filtros['codigo_provincia'])) {
            $query->where('provincias.codigo_provincia', $filtros['codigo_provincia']);
        }
        if (! empty($filtros['codigo_municipio'])) {
            $query->where('municipios.codigo_municipio', $filtros['codigo_municipio']);
        }
        if (! empty($filtros['codigo_localidad'])) {
            $query->where('localidades.codigo_localidad', $filtros['codigo_localidad']);
        }
        if (! empty($filtros['codigo_recinto'])) {
            $query->where('recintos.codigo_recinto', $filtros['codigo_recinto']);
        }
    }
}
