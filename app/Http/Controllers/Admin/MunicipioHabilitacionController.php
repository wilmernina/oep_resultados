<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Municipio;
use App\Models\OrgPolitica;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MunicipioHabilitacionController extends Controller
{
    public function index(Request $request): View
    {
        $municipios = Municipio::orderBy('nombre_municipio')->get(['codigo_municipio', 'nombre_municipio']);
        $codigoMunicipio = $request->integer('codigo_municipio') ?: $municipios->first()?->codigo_municipio;

        $habilitadas = [];
        if ($codigoMunicipio) {
            $habilitadas = DB::table('municipio_org_habilitaciones')
                ->where('codigo_municipio', $codigoMunicipio)
                ->pluck('codigo_organizacion')
                ->map(fn ($v) => (int) $v)
                ->all();
        }

        return view('admin.habilitaciones.index', [
            'municipios' => $municipios,
            'codigoMunicipio' => $codigoMunicipio,
            'organizaciones' => OrgPolitica::orderBy('nombre_organizacion')->get(['codigo_organizacion', 'nombre_organizacion', 'sigla', 'color_hex']),
            'habilitadas' => $habilitadas,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'codigo_municipio' => ['required', 'exists:municipios,codigo_municipio'],
            'organizaciones' => ['nullable', 'array'],
            'organizaciones.*' => ['integer', 'exists:org_politicas,codigo_organizacion'],
            'colores' => ['nullable', 'array'],
            'colores.*' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $organizaciones = collect($data['organizaciones'] ?? [])->map(fn ($v) => (int) $v)->unique()->values();
        $colores = collect($data['colores'] ?? [])
            ->mapWithKeys(fn ($color, $codigoOrg) => [(int) $codigoOrg => $color ? strtoupper((string) $color) : null]);

        DB::transaction(function () use ($data, $organizaciones, $colores): void {
            DB::table('municipio_org_habilitaciones')
                ->where('codigo_municipio', $data['codigo_municipio'])
                ->delete();

            if ($organizaciones->isNotEmpty()) {
                $now = now();
                $rows = $organizaciones->map(fn ($codigoOrg) => [
                    'codigo_municipio' => $data['codigo_municipio'],
                    'codigo_organizacion' => $codigoOrg,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('municipio_org_habilitaciones')->insert($rows);
            }

            foreach ($colores as $codigoOrg => $color) {
                OrgPolitica::where('codigo_organizacion', $codigoOrg)->update([
                    'color_hex' => $color,
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.habilitaciones.index', ['codigo_municipio' => $data['codigo_municipio']])
            ->with('ok', 'Habilitaciones y colores actualizados.');
    }

    public function resetOperacion(): RedirectResponse
    {
        DB::transaction(function (): void {
            DB::table('mesa_org_politica')->delete();
            DB::table('mesas')->update([
                'total_votos_validos' => 0,
                'votos_blancos' => 0,
                'votos_nulos' => 0,
                'total_votos_emitidos' => 0,
                'acta_imagen' => null,
                'updated_at' => now(),
            ]);
        });

        Storage::disk('public')->deleteDirectory('actas');
        Storage::disk('public')->makeDirectory('actas');

        return back()->with('ok', 'Operación reiniciada: votos y fotos de actas eliminados.');
    }

    public function reabrirMesa(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'codigo_mesa' => ['required', 'exists:mesas,codigo_mesa'],
        ]);

        DB::transaction(function () use ($data): void {
            DB::table('mesa_org_politica')->where('codigo_mesa', $data['codigo_mesa'])->delete();

            Mesa::where('codigo_mesa', $data['codigo_mesa'])->update([
                'total_votos_validos' => 0,
                'votos_blancos' => 0,
                'votos_nulos' => 0,
                'total_votos_emitidos' => 0,
                'acta_imagen' => null,
                'updated_at' => now(),
            ]);
        });

        return back()->with('ok', "Mesa {$data['codigo_mesa']} reabierta para edición.");
    }
}
