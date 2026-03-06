@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">CONTROLA TU VOTO</h4>
            <small class="text-muted">Computo Preliminar Electoral - Elecciones Subnacionales 2026</small>
        </div>
        @if(auth()->user()->canRegisterActa())
            <a href="{{ route('actas.create') }}" class="btn btn-success">Registrar Acta</a>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end" id="dashboardFilterForm">
                <div class="col-md-3">
                    <label class="form-label mb-1">Provincia</label>
                    <select name="codigo_provincia" id="codigo_provincia" class="form-select">
                        <option value="">Todas</option>
                        @foreach($opciones['provincias'] as $provincia)
                            <option value="{{ $provincia->codigo_provincia }}" @selected(($filtros['codigo_provincia'] ?? null) == $provincia->codigo_provincia)>
                                {{ $provincia->nombre_provincia }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Municipio</label>
                    <select name="codigo_municipio" id="codigo_municipio" class="form-select">
                        <option value="">Todos</option>
                        @foreach($opciones['municipios'] as $municipio)
                            <option value="{{ $municipio->codigo_municipio }}" @selected(($filtros['codigo_municipio'] ?? null) == $municipio->codigo_municipio)>
                                {{ $municipio->nombre_municipio }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Localidad</label>
                    <select name="codigo_localidad" id="codigo_localidad" class="form-select">
                        <option value="">Todas</option>
                        @foreach($opciones['localidades'] as $localidad)
                            <option value="{{ $localidad->codigo_localidad }}" @selected(($filtros['codigo_localidad'] ?? null) == $localidad->codigo_localidad)>
                                {{ $localidad->nombre_localidad }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Recinto</label>
                    <select name="codigo_recinto" id="codigo_recinto" class="form-select">
                        <option value="">Todos</option>
                        @foreach($opciones['recintos'] as $recinto)
                            <option value="{{ $recinto->codigo_recinto }}" @selected(($filtros['codigo_recinto'] ?? null) == $recinto->codigo_recinto)>
                                {{ $recinto->nombre_recinto }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-danger">Filtrar Resultados</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Total RegistroVotos</small>
                    <h3 class="mb-0">{{ number_format($resumen['validos']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">VotosBlancos</small>
                    <h3 class="mb-0">{{ number_format($resumen['blancos']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">VotosNulos</small>
                    <h3 class="mb-0">{{ number_format($resumen['nulos']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Actas Computadas</small>
                    <h3 class="mb-0">{{ $resumen['pct_actas'] }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">RegistroVotos por Org_politica</div>
                <div class="card-body">
                    <canvas id="chartBarras" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Procesamiento de Actas</div>
                <div class="card-body">
                    <canvas id="chartDona" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
const barrasLabels = @json($barras['labels']);
const barrasData = @json($barras['data']);
const barrasColorsRaw = @json($barras['colors'] ?? []);
const donaData = @json([$resumen['actas_computadas'], $resumen['pendientes_mesas']]);
const donutLabels = @json([
    'Computadas: '.$resumen['actas_computadas'],
    'Pendientes: '.$resumen['pendientes_mesas'],
]);
const provinciaSelect = document.getElementById('codigo_provincia');
const municipioSelect = document.getElementById('codigo_municipio');
const localidadSelect = document.getElementById('codigo_localidad');
const recintoSelect = document.getElementById('codigo_recinto');
const filterForm = document.getElementById('dashboardFilterForm');
const catalogoMunicipios = @json($catalogos['municipios']);
const catalogoLocalidades = @json($catalogos['localidades']);
const catalogoRecintos = @json($catalogos['recintos']);
const selectedMunicipio = @json($filtros['codigo_municipio']);
const selectedLocalidad = @json($filtros['codigo_localidad']);
const selectedRecinto = @json($filtros['codigo_recinto']);

function setOptions(select, items, valueKey, labelKey, placeholder, selectedValue = null) {
    select.innerHTML = '';
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = placeholder;
    select.appendChild(defaultOption);

    items.forEach((item) => {
        const option = document.createElement('option');
        option.value = item[valueKey];
        option.textContent = item[labelKey];
        if (selectedValue !== null && String(item[valueKey]) === String(selectedValue)) {
            option.selected = true;
        }
        select.appendChild(option);
    });
}

function cargarMunicipios(codigoProvincia, selected = null) {
    const data = codigoProvincia
        ? catalogoMunicipios.filter((m) => String(m.codigo_provincia) === String(codigoProvincia))
        : [];
    setOptions(municipioSelect, data, 'codigo_municipio', 'nombre_municipio', 'Todos', selected);
}

function cargarLocalidades(codigoMunicipio, selected = null) {
    const data = codigoMunicipio
        ? catalogoLocalidades.filter((l) => String(l.codigo_municipio) === String(codigoMunicipio))
        : [];
    setOptions(localidadSelect, data, 'codigo_localidad', 'nombre_localidad', 'Todas', selected);
}

function cargarRecintos(codigoLocalidad, selected = null) {
    const data = codigoLocalidad
        ? catalogoRecintos.filter((r) => String(r.codigo_localidad) === String(codigoLocalidad))
        : [];
    setOptions(recintoSelect, data, 'codigo_recinto', 'nombre_recinto', 'Todos', selected);
}

provinciaSelect.addEventListener('change', () => {
    cargarMunicipios(provinciaSelect.value);
    cargarLocalidades(null);
    cargarRecintos(null);
    filterForm.requestSubmit();
});

municipioSelect.addEventListener('change', () => {
    cargarLocalidades(municipioSelect.value);
    cargarRecintos(null);
    filterForm.requestSubmit();
});

localidadSelect.addEventListener('change', () => {
    cargarRecintos(localidadSelect.value);
    filterForm.requestSubmit();
});

recintoSelect.addEventListener('change', () => {
    filterForm.requestSubmit();
});

if (provinciaSelect.value) {
    cargarMunicipios(provinciaSelect.value, selectedMunicipio);
}
if (municipioSelect.value) {
    cargarLocalidades(municipioSelect.value, selectedLocalidad);
}
if (localidadSelect.value) {
    cargarRecintos(localidadSelect.value, selectedRecinto);
}

const barPaletteFallback = [
    '#0d6efd','#198754','#dc3545','#fd7e14','#6f42c1',
    '#20c997','#ffc107','#6610f2','#0dcaf0','#d63384',
];
const barColors = barrasLabels.map((_, i) => {
    const configured = barrasColorsRaw[i];
    return configured && /^#[0-9A-Fa-f]{6}$/.test(configured)
        ? configured
        : barPaletteFallback[i % barPaletteFallback.length];
});
if (window.ChartDataLabels) {
    Chart.register(ChartDataLabels);
}

new Chart(document.getElementById('chartBarras'), {
    type: 'bar',
    data: {
        labels: barrasLabels,
        datasets: [{
            label: 'RegistroVotos',
            data: barrasData,
            backgroundColor: barColors,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            datalabels: {
                anchor: 'end',
                align: 'end',
                color: '#111',
                font: { weight: '700' },
                formatter: (value) => Number(value).toLocaleString('es-BO')
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: (value) => Number(value).toLocaleString('es-BO')
                }
            }
        }
    }
});

new Chart(document.getElementById('chartDona'), {
    type: 'doughnut',
    data: {
        labels: donutLabels,
        datasets: [{
            data: donaData,
            backgroundColor: ['#0d6efd', '#dee2e6']
        }]
    },
    options: {
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom' },
            datalabels: {
                color: '#111',
                font: { weight: '700' },
                formatter: (value) => Number(value).toLocaleString('es-BO')
            },
            tooltip: {
                callbacks: {
                    label: (ctx) => `${ctx.label}`
                }
            }
        }
    }
});
</script>
@endsection
