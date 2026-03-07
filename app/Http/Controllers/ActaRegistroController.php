<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Mesa;
use App\Models\MesaOrgPolitica;
use App\Models\OrgPolitica;
use App\Models\Provincia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ActaRegistroController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $preseleccion = $this->obtenerPreseleccion($usuario);

        $filtros = [
            'codigo_departamento' => $request->integer('codigo_departamento') ?: $preseleccion['codigo_departamento'],
            'codigo_provincia' => $request->integer('codigo_provincia') ?: $preseleccion['codigo_provincia'],
            'codigo_municipio' => $request->integer('codigo_municipio') ?: $preseleccion['codigo_municipio'],
            'codigo_localidad' => $request->integer('codigo_localidad') ?: null,
            'codigo_recinto' => $request->integer('codigo_recinto') ?: null,
            'codigo_mesa' => $request->integer('codigo_mesa') ?: null,
        ];

        if ($usuario?->role === 'operador' && $preseleccion['codigo_municipio']) {
            $filtros['codigo_departamento'] = $preseleccion['codigo_departamento'];
            $filtros['codigo_provincia'] = $preseleccion['codigo_provincia'];
            $filtros['codigo_municipio'] = $preseleccion['codigo_municipio'];
        }

        $departamentos = Departamento::query()
            ->when($usuario?->role === 'operador' && $preseleccion['codigo_departamento'], function ($q) use ($preseleccion): void {
                $q->where('codigo_departamento', $preseleccion['codigo_departamento']);
            })
            ->orderBy('nombre_departamento')
            ->get(['codigo_departamento', 'nombre_departamento']);

        $provincias = collect();
        if ($filtros['codigo_departamento']) {
            $provincias = DB::table('provincias')
                ->where('codigo_departamento', $filtros['codigo_departamento'])
                ->when($usuario?->role === 'operador' && $preseleccion['codigo_provincia'], function ($q) use ($preseleccion): void {
                    $q->where('codigo_provincia', $preseleccion['codigo_provincia']);
                })
                ->orderBy('nombre_provincia')
                ->get(['codigo_provincia', 'nombre_provincia']);
        }

        $municipios = collect();
        if ($filtros['codigo_provincia']) {
            $municipios = DB::table('municipios')
                ->where('codigo_provincia', $filtros['codigo_provincia'])
                ->when($usuario?->role === 'operador' && $preseleccion['codigo_municipio'], function ($q) use ($preseleccion): void {
                    $q->where('codigo_municipio', $preseleccion['codigo_municipio']);
                })
                ->orderBy('nombre_municipio')
                ->get(['codigo_municipio', 'nombre_municipio']);
        }

        $localidades = $filtros['codigo_municipio']
            ? DB::table('localidades')
                ->where('codigo_municipio', $filtros['codigo_municipio'])
                ->orderBy('nombre_localidad')
                ->get(['codigo_localidad', 'nombre_localidad'])
            : collect();

        $recintos = $filtros['codigo_localidad']
            ? DB::table('recintos')
                ->where('codigo_localidad', $filtros['codigo_localidad'])
                ->orderBy('nombre_recinto')
                ->get(['codigo_recinto', 'nombre_recinto'])
            : collect();

        $mesas = $filtros['codigo_recinto']
            ? DB::table('mesas')
                ->where('mesas.codigo_recinto', $filtros['codigo_recinto'])
                ->orderBy('mesas.numero_mesa')
                ->get([
                    'mesas.codigo_mesa',
                    'mesas.numero_mesa',
                    DB::raw('(EXISTS(SELECT 1 FROM mesa_org_politica mop WHERE mop.codigo_mesa = mesas.codigo_mesa) OR mesas.acta_imagen IS NOT NULL) as registrada'),
                ])
            : collect();

        $acta = null;
        $votos = collect();
        $mensaje = null;

        if ($filtros['codigo_mesa']) {
            $acta = DB::table('mesas')
                ->join('recintos', 'recintos.codigo_recinto', '=', 'mesas.codigo_recinto')
                ->join('localidades', 'localidades.codigo_localidad', '=', 'recintos.codigo_localidad')
                ->join('municipios', 'municipios.codigo_municipio', '=', 'localidades.codigo_municipio')
                ->join('provincias', 'provincias.codigo_provincia', '=', 'municipios.codigo_provincia')
                ->join('departamentos', 'departamentos.codigo_departamento', '=', 'provincias.codigo_departamento')
                ->where('mesas.codigo_mesa', $filtros['codigo_mesa'])
                ->when($usuario?->role === 'operador' && $preseleccion['codigo_municipio'], function ($q) use ($preseleccion): void {
                    $q->where('municipios.codigo_municipio', $preseleccion['codigo_municipio']);
                })
                ->first([
                    'mesas.codigo_mesa',
                    'mesas.numero_mesa',
                    'mesas.total_votos_validos',
                    'mesas.votos_blancos',
                    'mesas.votos_nulos',
                    'mesas.total_votos_emitidos',
                    'mesas.acta_imagen',
                    'mesas.updated_at',
                    'departamentos.nombre_departamento',
                    'provincias.nombre_provincia',
                    'municipios.nombre_municipio',
                    'localidades.nombre_localidad',
                    'recintos.nombre_recinto',
                ]);

            if (! $acta) {
                $mensaje = 'No se encontró la mesa seleccionada o no tienes permisos para verla.';
            } else {
                $votos = DB::table('mesa_org_politica')
                    ->join('org_politicas', 'org_politicas.codigo_organizacion', '=', 'mesa_org_politica.codigo_organizacion')
                    ->where('mesa_org_politica.codigo_mesa', $filtros['codigo_mesa'])
                    ->orderByDesc('mesa_org_politica.registro_votos')
                    ->get([
                        'org_politicas.sigla',
                        'mesa_org_politica.registro_votos',
                    ]);

                if (! $acta->acta_imagen && $votos->isEmpty()) {
                    $mensaje = 'La mesa seleccionada aún no tiene acta registrada.';
                    $acta = null;
                }
            }
        }

        return view('actas.index', [
            'filtros' => $filtros,
            'departamentos' => $departamentos,
            'provincias' => $provincias,
            'municipios' => $municipios,
            'localidades' => $localidades,
            'recintos' => $recintos,
            'mesas' => $mesas,
            'acta' => $acta,
            'votos' => $votos,
            'mensaje' => $mensaje,
            'preseleccion' => $preseleccion,
        ]);
    }

    public function create(): View
    {
        $usuario = request()->user();
        $preseleccion = $this->obtenerPreseleccion($usuario);

        return view('actas.create', [
            'departamentos' => Departamento::orderBy('nombre_departamento')->get(),
            'provincias' => Provincia::orderBy('nombre_provincia')->get(),
            'organizaciones' => collect(),
            'preseleccion' => $preseleccion,
        ]);
    }

    public function provinciasPorDepartamento(int $codigoDepartamento): JsonResponse
    {
        $usuario = request()->user();
        if ($usuario?->role === 'admin') {
            $provincias = DB::table('provincias')
                ->where('codigo_departamento', $codigoDepartamento)
                ->orderBy('nombre_provincia')
                ->get([
                    'codigo_provincia',
                    'nombre_provincia',
                ]);
        } else {
            $provincias = DB::table('provincias')
                ->join('municipios', 'municipios.codigo_provincia', '=', 'provincias.codigo_provincia')
                ->join('municipio_org_habilitaciones', 'municipio_org_habilitaciones.codigo_municipio', '=', 'municipios.codigo_municipio')
                ->where('codigo_departamento', $codigoDepartamento)
                ->when($usuario?->codigo_municipio, function ($q) use ($usuario): void {
                    $q->where('municipios.codigo_municipio', $usuario->codigo_municipio);
                })
                ->orderBy('provincias.nombre_provincia')
                ->distinct()
                ->get([
                    'provincias.codigo_provincia',
                    'provincias.nombre_provincia',
                ]);
        }

        return response()->json($provincias);
    }

    public function municipiosPorDepartamento(int $codigoDepartamento): JsonResponse
    {
        $usuario = request()->user();
        if ($usuario?->role === 'admin') {
            $municipios = DB::table('municipios')
                ->join('provincias', 'provincias.codigo_provincia', '=', 'municipios.codigo_provincia')
                ->where('provincias.codigo_departamento', $codigoDepartamento)
                ->orderBy('municipios.nombre_municipio')
                ->get([
                    'municipios.codigo_municipio',
                    'municipios.nombre_municipio',
                ]);
        } else {
            $municipios = DB::table('municipios')
                ->join('provincias', 'provincias.codigo_provincia', '=', 'municipios.codigo_provincia')
                ->join('municipio_org_habilitaciones', 'municipio_org_habilitaciones.codigo_municipio', '=', 'municipios.codigo_municipio')
                ->where('provincias.codigo_departamento', $codigoDepartamento)
                ->when($usuario?->codigo_municipio, function ($q) use ($usuario): void {
                    $q->where('municipios.codigo_municipio', $usuario->codigo_municipio);
                })
                ->orderBy('municipios.nombre_municipio')
                ->distinct()
                ->get([
                    'municipios.codigo_municipio',
                    'municipios.nombre_municipio',
                ]);
        }

        return response()->json($municipios);
    }

    public function municipiosPorProvincia(int $codigoProvincia): JsonResponse
    {
        $usuario = request()->user();
        if ($usuario?->role === 'admin') {
            $municipios = DB::table('municipios')
                ->where('codigo_provincia', $codigoProvincia)
                ->orderBy('nombre_municipio')
                ->get([
                    'codigo_municipio',
                    'nombre_municipio',
                ]);
        } else {
            $municipios = DB::table('municipios')
                ->join('municipio_org_habilitaciones', 'municipio_org_habilitaciones.codigo_municipio', '=', 'municipios.codigo_municipio')
                ->where('codigo_provincia', $codigoProvincia)
                ->when($usuario?->codigo_municipio, function ($q) use ($usuario): void {
                    $q->where('municipios.codigo_municipio', $usuario->codigo_municipio);
                })
                ->orderBy('nombre_municipio')
                ->distinct()
                ->get([
                    'municipios.codigo_municipio',
                    'municipios.nombre_municipio',
                ]);
        }

        return response()->json($municipios);
    }

    public function organizacionesPorMunicipio(int $codigoMunicipio): JsonResponse
    {
        $organizaciones = OrgPolitica::query()
            ->join('municipio_org_habilitaciones', 'municipio_org_habilitaciones.codigo_organizacion', '=', 'org_politicas.codigo_organizacion')
            ->where('municipio_org_habilitaciones.codigo_municipio', $codigoMunicipio)
            ->orderBy('nombre_organizacion')
            ->get(['org_politicas.codigo_organizacion', 'org_politicas.nombre_organizacion', 'org_politicas.sigla']);

        return response()->json($organizaciones);
    }

    public function localidadesPorMunicipio(int $codigoMunicipio): JsonResponse
    {
        $localidades = DB::table('localidades')
            ->where('codigo_municipio', $codigoMunicipio)
            ->orderBy('nombre_localidad')
            ->get([
                'codigo_localidad',
                'nombre_localidad',
            ]);

        return response()->json($localidades);
    }

    public function recintosPorLocalidad(int $codigoLocalidad): JsonResponse
    {
        $recintos = DB::table('recintos')
            ->where('codigo_localidad', $codigoLocalidad)
            ->orderBy('nombre_recinto')
            ->get([
                'codigo_recinto',
                'nombre_recinto',
            ]);

        return response()->json($recintos);
    }

    public function mesasPorRecinto(int $codigoRecinto): JsonResponse
    {
        $mesas = DB::table('mesas')
            ->where('mesas.codigo_recinto', $codigoRecinto)
            ->orderBy('mesas.numero_mesa')
            ->get([
                'mesas.codigo_mesa',
                'mesas.numero_mesa',
                DB::raw('(EXISTS(SELECT 1 FROM mesa_org_politica mop WHERE mop.codigo_mesa = mesas.codigo_mesa) OR mesas.acta_imagen IS NOT NULL) as registrada'),
            ]);

        return response()->json($mesas);
    }

    public function detalleMesa(int $codigoMesa): JsonResponse
    {
        $usuario = request()->user();

        $mesa = DB::table('mesas')
            ->join('recintos', 'recintos.codigo_recinto', '=', 'mesas.codigo_recinto')
            ->join('localidades', 'localidades.codigo_localidad', '=', 'recintos.codigo_localidad')
            ->join('municipios', 'municipios.codigo_municipio', '=', 'localidades.codigo_municipio')
            ->where('mesas.codigo_mesa', $codigoMesa)
            ->when($usuario?->role === 'operador' && $usuario?->codigo_municipio, function ($q) use ($usuario): void {
                $q->where('municipios.codigo_municipio', $usuario->codigo_municipio);
            })
            ->first([
                'mesas.codigo_mesa',
                'mesas.votos_blancos',
                'mesas.votos_nulos',
                'mesas.total_votos_validos',
                'mesas.total_votos_emitidos',
                'mesas.acta_imagen',
            ]);

        if (! $mesa) {
            return response()->json(['message' => 'Mesa no encontrada o sin permiso.'], 404);
        }

        $votos = DB::table('mesa_org_politica')
            ->where('codigo_mesa', $codigoMesa)
            ->get(['codigo_organizacion', 'registro_votos']);

        return response()->json([
            'mesa' => $mesa,
            'votos' => $votos,
            'acta_imagen_url' => $mesa->acta_imagen ? asset('storage/'.$mesa->acta_imagen) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $editarExistente = $request->boolean('editar_existente');

        $data = $request->validate([
            'codigo_departamento' => ['required', 'exists:departamentos,codigo_departamento'],
            'codigo_provincia' => ['required', 'exists:provincias,codigo_provincia'],
            'codigo_municipio' => ['required', 'exists:municipios,codigo_municipio'],
            'codigo_localidad' => ['required', 'exists:localidades,codigo_localidad'],
            'codigo_recinto' => ['required', 'exists:recintos,codigo_recinto'],
            'codigo_mesa' => ['required', 'exists:mesas,codigo_mesa'],
            'votos_blancos' => ['required', 'integer', 'min:0'],
            'votos_nulos' => ['required', 'integer', 'min:0'],
            'votos' => ['required', 'array', 'min:1'],
            'votos.*.codigo_organizacion' => ['required', 'exists:org_politicas,codigo_organizacion'],
            'votos.*.registro_votos' => ['required', 'integer', 'min:0'],
            'acta_imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'editar_existente' => ['nullable', 'boolean'],
        ]);

        $usuario = $request->user();
        if ($usuario?->role === 'operador' && $usuario?->codigo_municipio && (int) $usuario->codigo_municipio !== (int) $data['codigo_municipio']) {
            return back()->withErrors(['codigo_municipio' => 'Tu usuario operador no está asignado a ese municipio.'])->withInput();
        }

        $codigosPermitidos = DB::table('municipio_org_habilitaciones')
            ->where('codigo_municipio', $data['codigo_municipio'])
            ->pluck('codigo_organizacion')
            ->map(fn ($v) => (int) $v)
            ->all();

        if (empty($codigosPermitidos)) {
            return back()->withErrors(['codigo_municipio' => 'Municipio sin organizaciones habilitadas.'])->withInput();
        }

        $mesaYaRegistrada = DB::table('mesas')
            ->leftJoin('mesa_org_politica', 'mesa_org_politica.codigo_mesa', '=', 'mesas.codigo_mesa')
            ->where('mesas.codigo_mesa', $data['codigo_mesa'])
            ->where(function ($q): void {
                $q->whereNotNull('mesas.acta_imagen')
                    ->orWhereNotNull('mesa_org_politica.codigo_organizacion');
            })
            ->exists();

        if ($mesaYaRegistrada) {
            if (! $editarExistente) {
                return back()->withErrors([
                    'codigo_mesa' => 'La mesa ya fue registrada. Marca "Editar acta existente" para actualizarla.',
                ])->withInput();
            }
        }

        foreach ($data['votos'] as $fila) {
            if (! in_array((int) $fila['codigo_organizacion'], $codigosPermitidos, true)) {
                return back()->withErrors(['votos' => 'Hay organizaciones no habilitadas para este municipio.'])->withInput();
            }
        }

        $mesaValida = DB::table('mesas')
            ->join('recintos', 'recintos.codigo_recinto', '=', 'mesas.codigo_recinto')
            ->join('localidades', 'localidades.codigo_localidad', '=', 'recintos.codigo_localidad')
            ->join('municipios', 'municipios.codigo_municipio', '=', 'localidades.codigo_municipio')
            ->join('provincias', 'provincias.codigo_provincia', '=', 'municipios.codigo_provincia')
            ->where('mesas.codigo_mesa', $data['codigo_mesa'])
            ->where('mesas.codigo_recinto', $data['codigo_recinto'])
            ->where('localidades.codigo_localidad', $data['codigo_localidad'])
            ->where('municipios.codigo_municipio', $data['codigo_municipio'])
            ->where('provincias.codigo_provincia', $data['codigo_provincia'])
            ->where('provincias.codigo_departamento', $data['codigo_departamento'])
            ->exists();

        if (! $mesaValida) {
            return back()
                ->withErrors(['codigo_mesa' => 'La mesa no pertenece al municipio/departamento seleccionado.'])
                ->withInput();
        }

        DB::transaction(function () use ($data, $request): void {
            $mesa = Mesa::lockForUpdate()->findOrFail($data['codigo_mesa']);
            $rutaImagen = $mesa->acta_imagen;

            if ($request->hasFile('acta_imagen')) {
                if ($mesa->acta_imagen) {
                    Storage::disk('public')->delete($mesa->acta_imagen);
                }

                $file = $request->file('acta_imagen');
                $extension = strtolower($file->getClientOriginalExtension());
                // Aseguramos que se guarde como jpg/jpeg para mejor compresión
                $nombreArchivo = 'actas/'.uniqid().'.jpg';

                // Procesamiento con Intervention Image v3
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file);

                // Redimensionar a 1200px de ancho manteniendo proporción (solo si es más grande)
                $image->scale(width: 1200);

                // Guardar como JPEG con calidad 75%
                $encoded = $image->toJpeg(75);
                Storage::disk('public')->put($nombreArchivo, $encoded);

                $rutaImagen = $nombreArchivo;
            }

            $totalValidos = (int) collect($data['votos'])->sum('registro_votos');
            $blancos = (int) $data['votos_blancos'];
            $nulos = (int) $data['votos_nulos'];
            $totalEmitidos = $totalValidos + $blancos + $nulos;

            $mesa->update([
                'total_votos_validos' => $totalValidos,
                'votos_blancos' => $blancos,
                'votos_nulos' => $nulos,
                'total_votos_emitidos' => $totalEmitidos,
                'acta_imagen' => $rutaImagen,
            ]);

            MesaOrgPolitica::where('codigo_mesa', $mesa->codigo_mesa)->delete();

            $insertData = collect($data['votos'])->map(fn ($fila) => [
                'codigo_mesa' => $mesa->codigo_mesa,
                'codigo_organizacion' => $fila['codigo_organizacion'],
                'registro_votos' => $fila['registro_votos'],
            ])->toArray();

            DB::table('mesa_org_politica')->insert($insertData);
        });

        $mensaje = $editarExistente ? 'Acta actualizada correctamente.' : 'Acta registrada correctamente.';

        return redirect()->route('actas.create')->with('ok', $mensaje);
    }

    private function obtenerPreseleccion($usuario): array
    {
        $preseleccion = [
            'codigo_departamento' => null,
            'codigo_provincia' => null,
            'codigo_municipio' => null,
        ];

        if ($usuario?->role === 'operador' && $usuario?->codigo_municipio) {
            $fila = DB::table('municipios')
                ->join('provincias', 'provincias.codigo_provincia', '=', 'municipios.codigo_provincia')
                ->where('municipios.codigo_municipio', $usuario->codigo_municipio)
                ->first([
                    'provincias.codigo_departamento',
                    'provincias.codigo_provincia',
                    'municipios.codigo_municipio',
                ]);

            if ($fila) {
                $preseleccion = [
                    'codigo_departamento' => (int) $fila->codigo_departamento,
                    'codigo_provincia' => (int) $fila->codigo_provincia,
                    'codigo_municipio' => (int) $fila->codigo_municipio,
                ];
            }
        }

        return $preseleccion;
    }
}
