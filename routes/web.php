<?php

use App\Http\Controllers\ActaRegistroController;
use App\Http\Controllers\Admin\MunicipioHabilitacionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware(['auth'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/actas', [ActaRegistroController::class, 'index'])->name('actas.index');
    Route::get('/api/provincias/{codigoProvincia}/municipios', [DashboardController::class, 'municipiosPorProvincia'])
        ->name('dashboard.api.municipios');
    Route::get('/api/municipios/{codigoMunicipio}/localidades', [DashboardController::class, 'localidadesPorMunicipio'])
        ->name('dashboard.api.localidades');
    Route::get('/api/localidades/{codigoLocalidad}/recintos', [DashboardController::class, 'recintosPorLocalidad'])
        ->name('dashboard.api.recintos');
    Route::get('/api/actas/departamentos/{codigoDepartamento}/provincias', [ActaRegistroController::class, 'provinciasPorDepartamento'])
        ->name('actas.api.provincias');
    Route::get('/api/actas/departamentos/{codigoDepartamento}/municipios', [ActaRegistroController::class, 'municipiosPorDepartamento'])
        ->name('actas.api.municipios.departamento');
    Route::get('/api/actas/provincias/{codigoProvincia}/municipios', [ActaRegistroController::class, 'municipiosPorProvincia'])
        ->name('actas.api.municipios.provincia');
    Route::get('/api/actas/municipios/{codigoMunicipio}/localidades', [ActaRegistroController::class, 'localidadesPorMunicipio'])
        ->name('actas.api.localidades');
    Route::get('/api/actas/municipios/{codigoMunicipio}/organizaciones', [ActaRegistroController::class, 'organizacionesPorMunicipio'])
        ->name('actas.api.organizaciones');
    Route::get('/api/actas/localidades/{codigoLocalidad}/recintos', [ActaRegistroController::class, 'recintosPorLocalidad'])
        ->name('actas.api.recintos');
    Route::get('/api/actas/recintos/{codigoRecinto}/mesas', [ActaRegistroController::class, 'mesasPorRecinto'])
        ->name('actas.api.mesas');
    Route::get('/api/actas/mesas/{codigoMesa}/detalle', [ActaRegistroController::class, 'detalleMesa'])
        ->name('actas.api.mesa.detalle');
});

Route::middleware(['auth', 'operator'])->group(function (): void {
    Route::get('/actas/registro', [ActaRegistroController::class, 'create'])->name('actas.create');
    Route::post('/actas/registro', [ActaRegistroController::class, 'store'])->name('actas.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');

    Route::get('/habilitaciones', [MunicipioHabilitacionController::class, 'index'])->name('habilitaciones.index');
    Route::post('/habilitaciones', [MunicipioHabilitacionController::class, 'update'])->name('habilitaciones.update');
    Route::post('/operacion/reset', [MunicipioHabilitacionController::class, 'resetOperacion'])->name('operacion.reset');
    Route::post('/operacion/reabrir-mesa', [MunicipioHabilitacionController::class, 'reabrirMesa'])->name('operacion.reabrir_mesa');
});
