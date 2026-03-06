@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Consulta de Actas Registradas</h4>
        @if(auth()->user()->canRegisterActa())
            <a href="{{ route('actas.create') }}" class="btn btn-success">Registrar Acta</a>
        @endif
    </div>

    <form method="GET" action="{{ route('actas.index') }}" class="card border-0 shadow-sm mb-4" id="actasConsultaForm">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Departamento</label>
                    <select name="codigo_departamento" id="codigo_departamento" class="form-select" @disabled(auth()->user()->isOperator())>
                        <option value="">Seleccione</option>
                        @foreach($departamentos as $dep)
                            <option value="{{ $dep->codigo_departamento }}" @selected(($filtros['codigo_departamento'] ?? null) == $dep->codigo_departamento)>
                                {{ $dep->nombre_departamento }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Provincia</label>
                    <select name="codigo_provincia" id="codigo_provincia" class="form-select" @disabled(auth()->user()->isOperator())>
                        <option value="">Seleccione</option>
                        @foreach($provincias as $provincia)
                            <option value="{{ $provincia->codigo_provincia }}" @selected(($filtros['codigo_provincia'] ?? null) == $provincia->codigo_provincia)>
                                {{ $provincia->nombre_provincia }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Municipio</label>
                    <select name="codigo_municipio" id="codigo_municipio" class="form-select" @disabled(auth()->user()->isOperator())>
                        <option value="">Seleccione</option>
                        @foreach($municipios as $municipio)
                            <option value="{{ $municipio->codigo_municipio }}" @selected(($filtros['codigo_municipio'] ?? null) == $municipio->codigo_municipio)>
                                {{ $municipio->nombre_municipio }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Localidad</label>
                    <select name="codigo_localidad" id="codigo_localidad" class="form-select">
                        <option value="">Seleccione</option>
                        @foreach($localidades as $localidad)
                            <option value="{{ $localidad->codigo_localidad }}" @selected(($filtros['codigo_localidad'] ?? null) == $localidad->codigo_localidad)>
                                {{ $localidad->nombre_localidad }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Recinto</label>
                    <select name="codigo_recinto" id="codigo_recinto" class="form-select">
                        <option value="">Seleccione</option>
                        @foreach($recintos as $recinto)
                            <option value="{{ $recinto->codigo_recinto }}" @selected(($filtros['codigo_recinto'] ?? null) == $recinto->codigo_recinto)>
                                {{ $recinto->nombre_recinto }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mesa</label>
                    <select name="codigo_mesa" id="codigo_mesa" class="form-select">
                        <option value="">Seleccione</option>
                        @foreach($mesas as $mesa)
                            <option value="{{ $mesa->codigo_mesa }}" @selected(($filtros['codigo_mesa'] ?? null) == $mesa->codigo_mesa)>
                                {{ $mesa->numero_mesa }}{{ (int) $mesa->registrada === 1 ? ' (Registrada)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2 justify-content-end">
            @if(auth()->user()->isOperator())
                <input type="hidden" name="codigo_departamento" value="{{ $filtros['codigo_departamento'] }}">
                <input type="hidden" name="codigo_provincia" value="{{ $filtros['codigo_provincia'] }}">
                <input type="hidden" name="codigo_municipio" value="{{ $filtros['codigo_municipio'] }}">
            @endif
            <a href="{{ route('actas.index') }}" class="btn btn-outline-secondary">Limpiar Búsqueda</a>
            <button class="btn btn-primary">Buscar</button>
        </div>
    </form>

    @if($mensaje)
        <div class="alert alert-warning">{{ $mensaje }}</div>
    @endif

    @if($acta)
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-danger text-white fw-semibold">Resultados de la Mesa {{ $acta->numero_mesa }}</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                            <tr>
                                <th class="px-3">Sigla</th>
                                <th class="text-end px-3">Presidente</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($votos as $fila)
                                <tr>
                                    <td class="px-3">{{ $fila->sigla }}</td>
                                    <td class="text-end px-3">{{ number_format($fila->registro_votos) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <th class="px-3">Votos Válidos</th>
                                <th class="text-end px-3">{{ number_format((int) $acta->total_votos_validos) }}</th>
                            </tr>
                            <tr>
                                <th class="px-3">Votos Blancos</th>
                                <th class="text-end px-3">{{ number_format((int) $acta->votos_blancos) }}</th>
                            </tr>
                            <tr>
                                <th class="px-3">Votos Nulos</th>
                                <th class="text-end px-3">{{ number_format((int) $acta->votos_nulos) }}</th>
                            </tr>
                            <tr>
                                <th class="px-3">Votos Emitidos</th>
                                <th class="text-end px-3">{{ number_format((int) $acta->total_votos_emitidos) }}</th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-muted small">
                        Fecha y hora del servidor: {{ now('America/La_Paz')->format('d/m/Y H:i:s') }}
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold">Vista Previa del Acta Registrada</div>
                    <div class="card-body bg-light-subtle">
                        @if($acta->acta_imagen)
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomOutBtn" title="Alejar" aria-label="Alejar">−</button>
                                <span class="fw-semibold small" id="zoomLevel">100%</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomInBtn" title="Acercar" aria-label="Acercar">+</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomResetBtn" title="Restablecer zoom" aria-label="Restablecer zoom">⟳</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="fitWidthBtn" title="Ajustar a ancho" aria-label="Ajustar a ancho">↔</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="rotateBtn" title="Rotar" aria-label="Rotar">⤾</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="fullscreenBtn" title="Pantalla completa" aria-label="Pantalla completa">⛶</button>
                                <a class="btn btn-sm btn-outline-secondary" id="downloadActaBtn" href="{{ asset('storage/'.$acta->acta_imagen) }}" download="acta_mesa_{{ $acta->numero_mesa }}" title="Descargar" aria-label="Descargar">⭳</a>
                            </div>
                            <div id="actaViewerCanvas" class="border rounded bg-white overflow-auto text-center p-2" style="height: 640px;">
                                <img
                                    src="{{ asset('storage/'.$acta->acta_imagen) }}"
                                    alt="Acta mesa {{ $acta->numero_mesa }}"
                                    id="actaViewerImage"
                                    class="img-fluid"
                                    style="transform-origin: center center; transition: transform .15s ease;"
                                >
                            </div>
                        @else
                            <div class="text-muted text-center">No hay imagen cargada para esta acta.</div>
                        @endif
                    </div>
                    <div class="card-footer small text-muted">
                        {{ $acta->nombre_departamento }} / {{ $acta->nombre_provincia }} / {{ $acta->nombre_municipio }} / {{ $acta->nombre_localidad }} / {{ $acta->nombre_recinto }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
const depSelect = document.getElementById('codigo_departamento');
const provSelect = document.getElementById('codigo_provincia');
const munSelect = document.getElementById('codigo_municipio');
const locSelect = document.getElementById('codigo_localidad');
const recSelect = document.getElementById('codigo_recinto');
const mesaSelect = document.getElementById('codigo_mesa');
const isOperator = @json(auth()->user()->isOperator());

const provinciasUrlTemplate = @json(route('actas.api.provincias', ['codigoDepartamento' => '__DEP__']));
const municipiosProvUrlTemplate = @json(route('actas.api.municipios.provincia', ['codigoProvincia' => '__PROV__']));
const localidadesUrlTemplate = @json(route('actas.api.localidades', ['codigoMunicipio' => '__MUN__']));
const recintosUrlTemplate = @json(route('actas.api.recintos', ['codigoLocalidad' => '__LOC__']));
const mesasUrlTemplate = @json(route('actas.api.mesas', ['codigoRecinto' => '__REC__']));

function setOptions(select, items, valueKey, labelBuilder, placeholder) {
    if (!select) return;
    select.innerHTML = '';
    const initial = document.createElement('option');
    initial.value = '';
    initial.textContent = placeholder;
    select.appendChild(initial);

    items.forEach((item) => {
        const option = document.createElement('option');
        option.value = item[valueKey];
        option.textContent = labelBuilder(item);
        select.appendChild(option);
    });
}

async function cargarProvincias(codigoDepartamento, selected = null) {
    setOptions(provSelect, [], 'codigo_provincia', (item) => item.nombre_provincia, 'Seleccione');
    setOptions(munSelect, [], 'codigo_municipio', (item) => item.nombre_municipio, 'Seleccione');
    setOptions(locSelect, [], 'codigo_localidad', (item) => item.nombre_localidad, 'Seleccione');
    setOptions(recSelect, [], 'codigo_recinto', (item) => item.nombre_recinto, 'Seleccione');
    setOptions(mesaSelect, [], 'codigo_mesa', (item) => item.numero_mesa, 'Seleccione');

    if (!codigoDepartamento) return;

    const response = await fetch(provinciasUrlTemplate.replace('__DEP__', codigoDepartamento));
    const data = await response.json();
    setOptions(provSelect, data, 'codigo_provincia', (item) => item.nombre_provincia, 'Seleccione');

    if (selected) {
        provSelect.value = String(selected);
    }
}

async function cargarMunicipios(codigoProvincia, selected = null) {
    setOptions(munSelect, [], 'codigo_municipio', (item) => item.nombre_municipio, 'Seleccione');
    setOptions(locSelect, [], 'codigo_localidad', (item) => item.nombre_localidad, 'Seleccione');
    setOptions(recSelect, [], 'codigo_recinto', (item) => item.nombre_recinto, 'Seleccione');
    setOptions(mesaSelect, [], 'codigo_mesa', (item) => item.numero_mesa, 'Seleccione');

    if (!codigoProvincia) return;

    const response = await fetch(municipiosProvUrlTemplate.replace('__PROV__', codigoProvincia));
    const data = await response.json();
    setOptions(munSelect, data, 'codigo_municipio', (item) => item.nombre_municipio, 'Seleccione');

    if (selected) {
        munSelect.value = String(selected);
    }
}

async function cargarLocalidades(codigoMunicipio, selected = null) {
    setOptions(locSelect, [], 'codigo_localidad', (item) => item.nombre_localidad, 'Seleccione');
    setOptions(recSelect, [], 'codigo_recinto', (item) => item.nombre_recinto, 'Seleccione');
    setOptions(mesaSelect, [], 'codigo_mesa', (item) => item.numero_mesa, 'Seleccione');

    if (!codigoMunicipio) return;

    const response = await fetch(localidadesUrlTemplate.replace('__MUN__', codigoMunicipio));
    const data = await response.json();
    setOptions(locSelect, data, 'codigo_localidad', (item) => item.nombre_localidad, 'Seleccione');

    if (selected) {
        locSelect.value = String(selected);
    }
}

async function cargarRecintos(codigoLocalidad, selected = null) {
    setOptions(recSelect, [], 'codigo_recinto', (item) => item.nombre_recinto, 'Seleccione');
    setOptions(mesaSelect, [], 'codigo_mesa', (item) => item.numero_mesa, 'Seleccione');

    if (!codigoLocalidad) return;

    const response = await fetch(recintosUrlTemplate.replace('__LOC__', codigoLocalidad));
    const data = await response.json();
    setOptions(recSelect, data, 'codigo_recinto', (item) => item.nombre_recinto, 'Seleccione');

    if (selected) {
        recSelect.value = String(selected);
    }
}

async function cargarMesas(codigoRecinto, selected = null) {
    setOptions(mesaSelect, [], 'codigo_mesa', (item) => item.numero_mesa, 'Seleccione');

    if (!codigoRecinto) return;

    const response = await fetch(mesasUrlTemplate.replace('__REC__', codigoRecinto));
    const data = await response.json();
    setOptions(mesaSelect, data, 'codigo_mesa', (item) => `${item.numero_mesa}${Number(item.registrada) === 1 ? ' (Registrada)' : ''}`, 'Seleccione');

    if (selected) {
        mesaSelect.value = String(selected);
    }
}

if (depSelect && !isOperator) {
    depSelect.addEventListener('change', async () => {
        await cargarProvincias(depSelect.value);
    });
}

if (provSelect && !isOperator) {
    provSelect.addEventListener('change', async () => {
        await cargarMunicipios(provSelect.value);
    });
}

if (munSelect) {
    munSelect.addEventListener('change', async () => {
        await cargarLocalidades(munSelect.value);
    });
}

if (locSelect) {
    locSelect.addEventListener('change', async () => {
        await cargarRecintos(locSelect.value);
    });
}

if (recSelect) {
    recSelect.addEventListener('change', async () => {
        await cargarMesas(recSelect.value);
    });
}

const actaViewerImage = document.getElementById('actaViewerImage');
if (actaViewerImage) {
    const actaViewerCanvas = document.getElementById('actaViewerCanvas');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomResetBtn = document.getElementById('zoomResetBtn');
    const fitWidthBtn = document.getElementById('fitWidthBtn');
    const rotateBtn = document.getElementById('rotateBtn');
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    const zoomLevel = document.getElementById('zoomLevel');
    let zoom = 1;
    let rotation = 0;

    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
    const updateTransform = () => {
        actaViewerImage.style.transform = `scale(${zoom}) rotate(${rotation}deg)`;
        if (zoomLevel) {
            zoomLevel.textContent = `${Math.round(zoom * 100)}%`;
        }
    };

    const fitToWidth = () => {
        if (!actaViewerImage.naturalWidth || !actaViewerCanvas) return;
        const availableWidth = Math.max((actaViewerCanvas.clientWidth || 0) - 24, 1);
        zoom = clamp(availableWidth / actaViewerImage.naturalWidth, 0.25, 4);
        rotation = 0;
        updateTransform();
    };

    zoomOutBtn?.addEventListener('click', () => {
        zoom = clamp(zoom - 0.1, 0.25, 4);
        updateTransform();
    });

    zoomInBtn?.addEventListener('click', () => {
        zoom = clamp(zoom + 0.1, 0.25, 4);
        updateTransform();
    });

    zoomResetBtn?.addEventListener('click', () => {
        zoom = 1;
        rotation = 0;
        updateTransform();
    });

    fitWidthBtn?.addEventListener('click', fitToWidth);

    rotateBtn?.addEventListener('click', () => {
        rotation = (rotation + 90) % 360;
        updateTransform();
    });

    fullscreenBtn?.addEventListener('click', async () => {
        if (!actaViewerCanvas) return;
        if (document.fullscreenElement) {
            await document.exitFullscreen();
            return;
        }
        await actaViewerCanvas.requestFullscreen();
    });

    const initAt100 = () => {
        zoom = 1;
        rotation = 0;
        updateTransform();
    };

    if (actaViewerImage.complete) {
        initAt100();
    } else {
        actaViewerImage.addEventListener('load', initAt100, { once: true });
    }
}
</script>
@endsection
