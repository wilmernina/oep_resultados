@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Administración de Usuarios y Roles</h4>

    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Nuevo Usuario</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
                @csrf
                <div class="col-md-3"><input name="name" class="form-control" placeholder="Nombre" required></div>
                <div class="col-md-3"><input name="email" type="email" class="form-control" placeholder="Correo" required></div>
                <div class="col-md-2"><input name="password" type="password" class="form-control" placeholder="Contraseña" required></div>
                <div class="col-md-2">
                    <select name="role" class="form-select" required>
                        <option value="operador">operador</option>
                        <option value="visor">visor</option>
                        <option value="admin">admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="codigo_municipio" class="form-select">
                        <option value="">Municipio (opcional)</option>
                        @foreach($municipios as $m)
                            <option value="{{ $m->codigo_municipio }}">{{ $m->nombre_municipio }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12"><button class="btn btn-success">Crear usuario</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Usuarios</div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Municipio</th>
                    <th>Actualizar</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $u)
                    <tr>
                        <form method="POST" action="{{ route('admin.users.update', $u) }}">
                            @csrf
                            @method('PUT')
                            <td><input name="name" class="form-control form-control-sm" value="{{ $u->name }}" required></td>
                            <td><input name="email" class="form-control form-control-sm" value="{{ $u->email }}" required></td>
                            <td>
                                <select name="role" class="form-select form-select-sm" required>
                                    <option value="operador" @selected($u->role === 'operador')>operador</option>
                                    <option value="visor" @selected($u->role === 'visor')>visor</option>
                                    <option value="admin" @selected($u->role === 'admin')>admin</option>
                                </select>
                            </td>
                            <td>
                                <select name="codigo_municipio" class="form-select form-select-sm">
                                    <option value="">Sin municipio</option>
                                    @foreach($municipios as $m)
                                        <option value="{{ $m->codigo_municipio }}" @selected($u->codigo_municipio == $m->codigo_municipio)>
                                            {{ $m->nombre_municipio }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="d-flex gap-2">
                                <input name="password" type="password" class="form-control form-control-sm" placeholder="Nueva contraseña">
                                <button class="btn btn-sm btn-primary">Guardar</button>
                            </td>
                        </form>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
