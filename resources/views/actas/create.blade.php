@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Registro de Acta de Mesa</h4>
        <a href="{{ route('actas.index') }}" class="btn btn-outline-secondary">Ver Actas Registradas</a>
    </div>

    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('actas.store') }}" enctype="multipart/form-data" class="card border-0 shadow-sm">
        @csrf
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Departamento</label>
                    <select name="codigo_departamento" id="codigo_departamento" class="form-select" required>
                        <option value="">Seleccione departamento</option>
                        @foreach($departamentos as $dep)
                            <option value="{{ $dep->codigo_departamento }}" @selected(old('codigo_departamento', $preseleccion['codigo_departamento']) == $dep->codigo_departamento)>
                                {{ $dep->nombre_departamento }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Provincia</label>
                    <select name="codigo_provincia" id="codigo_provincia" class="form-select" required>
                        <option value="">Seleccione provincia</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Municipio</label>
                    <select name="codigo_municipio" id="codigo_municipio" class="form-select" required>
                        <option value="">Seleccione municipio</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Localidad</label>
                    <select name="codigo_localidad" id="codigo_localidad" class="form-select" required>
                        <option value="">Seleccione localidad</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Recinto</label>
                    <select name="codigo_recinto" id="codigo_recinto" class="form-select" required>
                        <option value="">Seleccione recinto</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">CodigoMesa / NumeroMesa</label>
                    <select name="codigo_mesa" id="codigo_mesa" class="form-select" required>
                        <option value="">Seleccione mesa</option>
                    </select>
                    <div class="mt-2">
                        <span id="mesa_estado_badge" class="badge text-bg-secondary">Seleccione mesa</span>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="editar_existente" name="editar_existente" @checked(old('editar_existente')) disabled>
                        <label class="form-check-label" for="editar_existente">
                            Editar acta existente
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">TotalVotosValidos (auto)</label>
                    <div class="form-control bg-light fw-semibold" id="total_votos_validos_mostrado">0</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">VotosBlancos</label>
                    <input type="number" name="votos_blancos" id="votos_blancos" min="0" value="{{ old('votos_blancos', 0) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">VotosNulos</label>
                    <input type="number" name="votos_nulos" id="votos_nulos" min="0" value="{{ old('votos_nulos', 0) }}" class="form-control" required>
                </div>
            </div>

            <div class="table-responsive mb-3">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>NombreOrganizacion</th>
                        <th>Sigla</th>
                        <th>RegistroVotos</th>
                    </tr>
                    </thead>
                    <tbody id="organizaciones_body">
                    </tbody>
                </table>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Foto del Acta (opcional)</label>
                    <input type="file" name="acta_imagen" id="acta_imagen" class="form-control" accept="image/*" capture="environment">
                    <small class="text-muted">Formatos: JPG, JPEG, PNG, WEBP. Máximo 20MB.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Vista previa</label>
                    <div class="border rounded p-2 bg-white text-center">
                        <img id="acta_preview" src="" alt="Vista previa acta" style="max-height: 220px; display: none;">
                        <div id="acta_preview_placeholder" class="text-muted">Sin imagen seleccionada</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end align-items-center">
            <button class="btn btn-success">Guardar Acta</button>
        </div>
    </form>
</div>

<script>
const depSelect = document.getElementById('codigo_departamento');
const provSelect = document.getElementById('codigo_provincia');
const munSelect = document.getElementById('codigo_municipio');
const locSelect = document.getElementById('codigo_localidad');
const recSelect = document.getElementById('codigo_recinto');
const mesaSelect = document.getElementById('codigo_mesa');
const editarExistenteInput = document.getElementById('editar_existente');
const mesaEstadoBadge = document.getElementById('mesa_estado_badge');
const votosBlancosInput = document.getElementById('votos_blancos');
const votosNulosInput = document.getElementById('votos_nulos');
const oldProvincia = @json(old('codigo_provincia', $preseleccion['codigo_provincia']));
const oldMunicipio = @json(old('codigo_municipio', $preseleccion['codigo_municipio']));
const oldLocalidad = @json(old('codigo_localidad'));
const oldRecinto = @json(old('codigo_recinto'));
const oldMesa = @json(old('codigo_mesa'));
const provinciasUrlTemplate = @json(route('actas.api.provincias', ['codigoDepartamento' => '__DEP__']));
const municipiosProvUrlTemplate = @json(route('actas.api.municipios.provincia', ['codigoProvincia' => '__PROV__']));
const localidadesUrlTemplate = @json(route('actas.api.localidades', ['codigoMunicipio' => '__MUN__']));
const recintosUrlTemplate = @json(route('actas.api.recintos', ['codigoLocalidad' => '__LOC__']));
const mesasUrlTemplate = @json(route('actas.api.mesas', ['codigoRecinto' => '__REC__']));
const organizacionesUrlTemplate = @json(route('actas.api.organizaciones', ['codigoMunicipio' => '__MUN__']));
const detalleMesaUrlTemplate = @json(route('actas.api.mesa.detalle', ['codigoMesa' => '__MESA__']));

const oldVotos = @json(old('votos'));
const oldVotosBlancos = @json(old('votos_blancos'));
const oldVotosNulos = @json(old('votos_nulos'));
let yaCargadoOld = false;

function setOptions(select, items, valueKey, labelKey, placeholder) {
    select.innerHTML = '';
    const initial = document.createElement('option');
    initial.value = '';
    initial.textContent = placeholder;
    select.appendChild(initial);

    items.forEach((item) => {
        const option = document.createElement('option');
        option.value = item[valueKey];
        option.textContent = item[labelKey];
        select.appendChild(option);
    });
}

function resetDetalleMesaFormulario() {
    document.querySelectorAll('input[name^="votos"][name$="[registro_votos]"]').forEach((input) => {
        input.value = 0;
    });
    if (votosBlancosInput) votosBlancosInput.value = 0;
    if (votosNulosInput) votosNulosInput.value = 0;

    if (actaInput && !(actaInput.files && actaInput.files.length)) {
        actaPreview.style.display = 'none';
        actaPreview.src = '';
        actaPlaceholder.style.display = 'block';
    }

    recalcularTotalValidos();
}

async function cargarDetalleMesa(codigoMesa) {
    if (!codigoMesa) {
        resetDetalleMesaFormulario();
        return;
    }

    // Si tenemos valores old y es la primera carga tras error, priorizamos old
    if (oldVotos && !yaCargadoOld && String(oldMesa) === String(codigoMesa)) {
        aplicarValoresOld();
        yaCargadoOld = true;
        return;
    }

    const url = detalleMesaUrlTemplate.replace('__MESA__', codigoMesa);
    const response = await fetch(url);
    if (!response.ok) {
        resetDetalleMesaFormulario();
        return;
    }

    const payload = await response.json();
    const votosPorOrg = {};
    (payload.votos || []).forEach((item) => {
        votosPorOrg[String(item.codigo_organizacion)] = Number(item.registro_votos || 0);
    });

    document.querySelectorAll('input[name^="votos"][name$="[registro_votos]"]').forEach((input) => {
        const codigo = input.dataset.codigoOrg;
        input.value = Object.prototype.hasOwnProperty.call(votosPorOrg, String(codigo))
            ? votosPorOrg[String(codigo)]
            : 0;
    });

    if (votosBlancosInput) votosBlancosInput.value = Number(payload.mesa?.votos_blancos || 0);
    if (votosNulosInput) votosNulosInput.value = Number(payload.mesa?.votos_nulos || 0);
    recalcularTotalValidos();

    if (payload.acta_imagen_url && !actaInput.files?.length) {
        actaPreview.src = payload.acta_imagen_url;
        actaPreview.style.display = 'inline-block';
        actaPlaceholder.style.display = 'none';
    } else if (!actaInput.files?.length) {
        actaPreview.style.display = 'none';
        actaPreview.src = '';
        actaPlaceholder.style.display = 'block';
    }
}

function aplicarValoresOld() {
    if (!oldVotos) return;
    
    const votosMap = {};
    oldVotos.forEach(v => {
        votosMap[String(v.codigo_organizacion)] = v.registro_votos;
    });

    document.querySelectorAll('input[name^="votos"][name$="[registro_votos]"]').forEach((input) => {
        const codigo = input.dataset.codigoOrg;
        if (votosMap[String(codigo)] !== undefined) {
            input.value = votosMap[String(codigo)];
        }
    });

    if (votosBlancosInput && oldVotosBlancos !== null) votosBlancosInput.value = oldVotosBlancos;
    if (votosNulosInput && oldVotosNulos !== null) votosNulosInput.value = oldVotosNulos;
    recalcularTotalValidos();
}

async function cargarProvincias(codigoDepartamento, selectedProvincia = null) {
    setOptions(provSelect, [], 'codigo_provincia', 'nombre_provincia', 'Seleccione provincia');
    setOptions(munSelect, [], 'codigo_municipio', 'nombre_municipio', 'Seleccione municipio');
    setOptions(locSelect, [], 'codigo_localidad', 'nombre_localidad', 'Seleccione localidad');
    setOptions(recSelect, [], 'codigo_recinto', 'nombre_recinto', 'Seleccione recinto');
    setOptions(mesaSelect, [], 'codigo_mesa', 'codigo_mesa', 'Seleccione mesa');

    if (!codigoDepartamento) return;

    const url = provinciasUrlTemplate.replace('__DEP__', codigoDepartamento);
    const response = await fetch(url);
    const data = await response.json();
    setOptions(provSelect, data, 'codigo_provincia', 'nombre_provincia', 'Seleccione provincia');

    if (selectedProvincia) {
        provSelect.value = String(selectedProvincia);
        await cargarMunicipios(selectedProvincia, oldMunicipio);
    }
}

async function cargarMunicipios(codigoProvincia, selectedMunicipio = null) {
    setOptions(munSelect, [], 'codigo_municipio', 'nombre_municipio', 'Seleccione municipio');
    setOptions(locSelect, [], 'codigo_localidad', 'nombre_localidad', 'Seleccione localidad');
    setOptions(recSelect, [], 'codigo_recinto', 'nombre_recinto', 'Seleccione recinto');
    setOptions(mesaSelect, [], 'codigo_mesa', 'codigo_mesa', 'Seleccione mesa');
    await cargarOrganizaciones(null);

    if (!codigoProvincia) return;

    const url = municipiosProvUrlTemplate.replace('__PROV__', codigoProvincia);
    const response = await fetch(url);
    const data = await response.json();
    setOptions(munSelect, data, 'codigo_municipio', 'nombre_municipio', 'Seleccione municipio');

    if (selectedMunicipio) {
        munSelect.value = String(selectedMunicipio);
        await cargarLocalidades(selectedMunicipio, oldLocalidad);
    }
}

async function cargarLocalidades(codigoMunicipio, selectedLocalidad = null) {
    setOptions(locSelect, [], 'codigo_localidad', 'nombre_localidad', 'Seleccione localidad');
    setOptions(recSelect, [], 'codigo_recinto', 'nombre_recinto', 'Seleccione recinto');
    setOptions(mesaSelect, [], 'codigo_mesa', 'codigo_mesa', 'Seleccione mesa');
    await cargarOrganizaciones(codigoMunicipio);

    if (!codigoMunicipio) return;

    const url = localidadesUrlTemplate.replace('__MUN__', codigoMunicipio);
    const response = await fetch(url);
    const data = await response.json();
    setOptions(locSelect, data, 'codigo_localidad', 'nombre_localidad', 'Seleccione localidad');

    if (selectedLocalidad) {
        locSelect.value = String(selectedLocalidad);
        await cargarRecintos(selectedLocalidad, oldRecinto);
    }
}

async function cargarOrganizaciones(codigoMunicipio) {
    const body = document.getElementById('organizaciones_body');
    body.innerHTML = '';

    if (!codigoMunicipio) {
        recalcularTotalValidos();
        return;
    }

    const url = organizacionesUrlTemplate.replace('__MUN__', codigoMunicipio);
    const response = await fetch(url);
    const data = await response.json();

    const votosMap = {};
    if (oldVotos && !yaCargadoOld && String(oldMunicipio) === String(codigoMunicipio)) {
        oldVotos.forEach(v => {
            votosMap[String(v.codigo_organizacion)] = v.registro_votos;
        });
    }

    data.forEach((org, i) => {
        const valor = votosMap[String(org.codigo_organizacion)] !== undefined ? votosMap[String(org.codigo_organizacion)] : 0;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <input type="hidden" name="votos[${i}][codigo_organizacion]" value="${org.codigo_organizacion}">
            <td>${org.nombre_organizacion}</td>
            <td>${org.sigla}</td>
            <td><input type="number" name="votos[${i}][registro_votos]" data-codigo-org="${org.codigo_organizacion}" min="0" value="${valor}" class="form-control" required></td>
        `;
        body.appendChild(tr);
    });

    attachTotalListener();
    recalcularTotalValidos();
}

async function cargarRecintos(codigoLocalidad, selectedRecinto = null) {
    setOptions(recSelect, [], 'codigo_recinto', 'nombre_recinto', 'Seleccione recinto');
    setOptions(mesaSelect, [], 'codigo_mesa', 'codigo_mesa', 'Seleccione mesa');

    if (!codigoLocalidad) return;

    const url = recintosUrlTemplate.replace('__LOC__', codigoLocalidad);
    const response = await fetch(url);
    const data = await response.json();
    setOptions(recSelect, data, 'codigo_recinto', 'nombre_recinto', 'Seleccione recinto');

    if (selectedRecinto) {
        recSelect.value = String(selectedRecinto);
        await cargarMesas(selectedRecinto, oldMesa);
    }
}

async function cargarMesas(codigoRecinto, selectedMesa = null) {
    setOptions(mesaSelect, [], 'codigo_mesa', 'codigo_mesa', 'Seleccione mesa');

    if (!codigoRecinto) return;

    const url = mesasUrlTemplate.replace('__REC__', codigoRecinto);
    const response = await fetch(url);
    const data = await response.json();
    mesaSelect.innerHTML = '<option value=\"\">Seleccione mesa</option>';
    data.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.codigo_mesa;
        const registrada = Number(item.registrada) === 1;
        option.dataset.registrada = registrada ? '1' : '0';
        option.textContent = registrada ? `${item.numero_mesa} (Registrada)` : `${item.numero_mesa}`;
        mesaSelect.appendChild(option);
    });

    if (selectedMesa) {
        mesaSelect.value = String(selectedMesa);
        syncEditarExistenteCheckbox();
        const selectedOption = mesaSelect.options[mesaSelect.selectedIndex];
        const isRegistrada = selectedOption && selectedOption.dataset.registrada === '1';
        if (isRegistrada) {
            await cargarDetalleMesa(selectedMesa);
        } else {
            resetDetalleMesaFormulario();
        }
    } else {
        syncEditarExistenteCheckbox();
        resetDetalleMesaFormulario();
    }
}

depSelect.addEventListener('change', async () => {
    await cargarProvincias(depSelect.value);
});

provSelect.addEventListener('change', async () => {
    await cargarMunicipios(provSelect.value);
});

munSelect.addEventListener('change', async () => {
    await cargarLocalidades(munSelect.value);
});

locSelect.addEventListener('change', async () => {
    await cargarRecintos(locSelect.value);
});

recSelect.addEventListener('change', async () => {
    await cargarMesas(recSelect.value);
});

mesaSelect.addEventListener('change', () => {
    syncEditarExistenteCheckbox();
    const selectedOption = mesaSelect.options[mesaSelect.selectedIndex];
    const isRegistrada = selectedOption && selectedOption.dataset.registrada === '1';
    if (isRegistrada) {
        cargarDetalleMesa(mesaSelect.value);
    } else {
        resetDetalleMesaFormulario();
    }
});

function syncEditarExistenteCheckbox() {
    const selectedOption = mesaSelect.options[mesaSelect.selectedIndex];
    if (!selectedOption || !editarExistenteInput) return;

    const isRegistrada = selectedOption.dataset.registrada === '1';
    // No deshabilitamos si está registrada, para que se envíe el valor
    editarExistenteInput.disabled = !isRegistrada;
    editarExistenteInput.checked = isRegistrada;

    if (!mesaEstadoBadge) return;
    if (!mesaSelect.value) {
        mesaEstadoBadge.className = 'badge text-bg-secondary';
        mesaEstadoBadge.textContent = 'Seleccione mesa';
        return;
    }

    if (isRegistrada) {
        mesaEstadoBadge.className = 'badge text-bg-warning';
        mesaEstadoBadge.textContent = 'Mesa registrada';
    } else {
        mesaEstadoBadge.className = 'badge text-bg-success';
        mesaEstadoBadge.textContent = 'Mesa nueva';
    }
}

if (depSelect.value) {
    cargarProvincias(depSelect.value, oldProvincia);
}

function recalcularTotalValidos() {
    const inputs = document.querySelectorAll('input[name^="votos"][name$="[registro_votos]"]');
    let total = 0;
    inputs.forEach((input) => {
        total += Number(input.value || 0);
    });
    document.getElementById('total_votos_validos_mostrado').textContent = total.toLocaleString('es-BO');
}

function attachTotalListener() {
    document.querySelectorAll('input[name^="votos"][name$="[registro_votos]"]').forEach((input) => {
        input.addEventListener('input', recalcularTotalValidos);
    });
}

attachTotalListener();
recalcularTotalValidos();

const actaInput = document.getElementById('acta_imagen');
const actaPreview = document.getElementById('acta_preview');
const actaPlaceholder = document.getElementById('acta_preview_placeholder');

actaInput.addEventListener('change', () => {
    const file = actaInput.files && actaInput.files[0];
    if (!file) {
        actaPreview.style.display = 'none';
        actaPreview.src = '';
        actaPlaceholder.style.display = 'block';
        return;
    }

    const url = URL.createObjectURL(file);
    actaPreview.src = url;
    actaPreview.style.display = 'inline-block';
    actaPlaceholder.style.display = 'none';
});
</script>
@endsection
