@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Habilitación Municipio - Organización Política</h4>
        <form method="POST" action="{{ route('admin.operacion.reset') }}" onsubmit="return confirm('Esta acción eliminará votos registrados y fotos de actas. ¿Deseas continuar?');">
            @csrf
            <button class="btn btn-danger">Reiniciar Operación</button>
        </form>
    </div>

    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.habilitaciones.index') }}" class="row g-3 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label">Municipio</label>
                    <select name="codigo_municipio" class="form-select" onchange="this.form.submit()">
                        @foreach($municipios as $m)
                            <option value="{{ $m->codigo_municipio }}" @selected($codigoMunicipio == $m->codigo_municipio)>{{ $m->nombre_municipio }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if($codigoMunicipio)
                <form method="POST" action="{{ route('admin.habilitaciones.update') }}">
                    @csrf
                    <input type="hidden" name="codigo_municipio" value="{{ $codigoMunicipio }}">

                    <div class="row g-2">
                        @foreach($organizaciones as $org)
                            <div class="col-md-4">
                                <div class="border rounded p-2 w-100 h-100">
                                    <label class="form-check mb-2">
                                        <input class="form-check-input me-2" type="checkbox" name="organizaciones[]" value="{{ $org->codigo_organizacion }}"
                                            @checked(in_array($org->codigo_organizacion, $habilitadas))>
                                        <span class="form-check-label">{{ $org->sigla }} - {{ $org->nombre_organizacion }}</span>
                                    </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="form-label mb-0 small text-muted">Color gráfico</label>
                                        <input
                                            type="color"
                                            class="form-control form-control-color"
                                            name="colores[{{ $org->codigo_organizacion }}]"
                                            value="{{ $org->color_hex ?: '#6C757D' }}"
                                            title="Seleccionar color de {{ $org->sigla }}"
                                        >
                                        <span class="small text-muted">{{ $org->color_hex ?: '#6C757D' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-success">Guardar habilitaciones</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white fw-semibold">Reabrir Mesa Específica</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.operacion.reabrir_mesa') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">CodigoMesa</label>
                    <input name="codigo_mesa" type="number" class="form-control" placeholder="Ej: 2140206200269001" required>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-warning" onclick="return confirm('Se limpiará el registro y foto de esta mesa. ¿Continuar?')">Reabrir Mesa</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
