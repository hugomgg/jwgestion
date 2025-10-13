<?php

use Illuminate\Support\Facades\Route;

// Incluir rutas de prueba en desarrollo (limpiado - archivos eliminados)

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return view('auth.login');
});

// Rutas de autenticación con reCAPTCHA en login
Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->middleware('recaptcha');
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Otras rutas de autenticación (registro, recuperación de contraseña, etc.)
Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// Ruta de exportar PDF de usuarios (solo para administradores)
Route::middleware(['auth', 'admin'])->get('/usuarios/exportar-pdf', [App\Http\Controllers\UserController::class, 'exportPdf'])->name('users.export.pdf');

Route::middleware('auth')->group(function () {
    // Ruta accesible para todos los usuarios autenticados
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // Rutas para obtener años y meses de programas (accesibles para usuarios autenticados)
    Route::get('/programas/anios-disponibles', [App\Http\Controllers\ProgramaController::class, 'getAniosDisponibles'])->name('programas.anios-disponibles');
    Route::get('/programas/meses-disponibles/{anio}', [App\Http\Controllers\ProgramaController::class, 'getMesesDisponibles'])->name('programas.meses-disponibles');

    // Exportar PDF de programas (para coordinadores)
    Route::get('/programas/exportar-pdf', [App\Http\Controllers\ProgramaController::class, 'exportPdf'])->name('programas.export.pdf');

    // Exportar XLS de programas (para coordinadores)
    Route::get('/programas/exportar-xls', [App\Http\Controllers\ProgramaController::class, 'exportXls'])->name('programas.export.xls');

    // Exportar Asignaciones de programas (para coordinadores)
    Route::get('/programas/exportar-asignaciones', [App\Http\Controllers\ProgramaController::class, 'exportAsignaciones'])->name('programas.export.asignaciones');

    // Rutas de lectura para usuarios (perfil 1, 2, 3 y 4)
    Route::middleware('can:can.view.users')->group(function () {
        // Gesti?n de Usuarios - Lectura (perfil 1, 2, 3 y 4)
        Route::get('/usuarios', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');

        // Visualizaci?n de usuarios (para perfil 1, 2, 3 y 4)
        Route::get('/usuarios/{id}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::get('/usuarios/{id}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    });

    // Rutas de lectura que requieren acceso al men? de administraci?n (perfil 1, 2 y 3)
    Route::middleware(['auth', 'can.access.admin.menu'])->group(function () {

        // Gesti?n de Perfiles - Lectura
        Route::get('/perfiles', [App\Http\Controllers\PerfilController::class, 'index'])->name('perfiles.index');

        // Gesti?n de Asignaciones - Lectura
        Route::get('/asignaciones', [App\Http\Controllers\AsignacionController::class, 'index'])->name('asignaciones.index');

        // Gesti?n de Sexo - Lectura
        Route::get('/sexo', [App\Http\Controllers\SexoController::class, 'index'])->name('sexo.index');

        // Gesti?n de Nombramiento - Lectura (perfil 1 y 2)
        Route::get('/nombramiento', [App\Http\Controllers\NombramientoController::class, 'index'])->name('nombramiento.index');

        // Gesti?n de Servicios - Lectura (perfil 1 y 2)
        Route::get('/servicios', [App\Http\Controllers\ServicioController::class, 'index'])->name('servicios.index');

        // Gesti?n de Programas - Para coordinadores (perfil 3)
        Route::get('/programas', [App\Http\Controllers\ProgramaController::class, 'index'])->name('programas.index');
        Route::get('/programas/{id}', [App\Http\Controllers\ProgramaController::class, 'show'])->name('programas.show');
    });

    // Actualizaci?n de perfil del usuario (para todos los perfiles autenticados)
    Route::put('/profile/update', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('profile.update');

    // Rutas para coordinadores y secretarios que pueden editar su propia congregaci?n
    Route::middleware('can:can.edit.own.congregation')->group(function () {
        Route::put('/congregacion/update', [App\Http\Controllers\CongregacionController::class, 'updateOwn'])->name('congregacion.update.own');
    });

    // Rutas de escritura para usuarios que requieren permisos de administrador (perfil 1), coordinador (perfil 3) o secretario (perfil 5)
    Route::middleware('can:can.view.users')->group(function () {
        // Gesti?n de Usuarios - Escritura (perfil 1, 3 y 5)
        Route::post('/usuarios', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::put('/usuarios/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('/usuarios/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    });

    // Rutas de escritura para programas - Para coordinadores (perfil 3)
    Route::middleware(['auth', 'can.access.admin.menu'])->group(function () {
        Route::get('/programas/{id}/edit', [App\Http\Controllers\ProgramaController::class, 'edit'])->name('programas.edit');
        Route::post('/programas', [App\Http\Controllers\ProgramaController::class, 'store'])->name('programas.store');
        Route::put('/programas/{id}', [App\Http\Controllers\ProgramaController::class, 'update'])->name('programas.update');
        Route::delete('/programas/{id}', [App\Http\Controllers\ProgramaController::class, 'destroy'])->name('programas.destroy');
    });

    // Rutas para PartePrograma - Para coordinadores (perfil 3)
    Route::middleware(['auth', 'can.access.admin.menu'])->group(function () {
        Route::get('/programas/{programaId}/partes', [App\Http\Controllers\ParteProgramaController::class, 'getPartesPorPrograma'])->name('partes-programa.get');
        Route::get('/programas/{programaId}/partes-segunda-seccion', [App\Http\Controllers\ProgramaController::class, 'getPartesSegundaSeccion'])->name('programas.partes-segunda-seccion');
        Route::get('/programas/{programaId}/partes-segunda-seccion-disponibles', [App\Http\Controllers\ProgramaController::class, 'getPartesSegundaSeccionDisponibles'])->name('programas.partes-segunda-seccion-disponibles');
        Route::get('/programas/{programaId}/partes-tercera-seccion', [App\Http\Controllers\ParteProgramaController::class, 'getPartesTerceraSeccion'])->name('programas.partes-tercera-seccion');
        Route::get('/programas/{programaId}/partes-nv', [App\Http\Controllers\ParteProgramaController::class, 'getPartesNV'])->name('programas.partes-nv');
        Route::get('/historial-usuario/{usuarioId}', [App\Http\Controllers\ParteProgramaController::class, 'getHistorialUsuario'])->name('historial-usuario.get');

        Route::post('/partes-programa', [App\Http\Controllers\ParteProgramaController::class, 'store'])->name('partes-programa.store');
        Route::get('/partes-programa/{id}', [App\Http\Controllers\ParteProgramaController::class, 'show'])->name('partes-programa.show');
        Route::put('/partes-programa/{id}', [App\Http\Controllers\ParteProgramaController::class, 'update'])->name('partes-programa.update');
        Route::delete('/partes-programa/{id}', [App\Http\Controllers\ParteProgramaController::class, 'destroy'])->name('partes-programa.destroy');
        Route::get('/partes-secciones', [App\Http\Controllers\ParteProgramaController::class, 'getPartesSecciones'])->name('partes-secciones.get');
        Route::get('/partes-seccion/{id}', [App\Http\Controllers\ParteProgramaController::class, 'getParteSeccion'])->name('partes-seccion.get');
        Route::get('/usuarios-por-parte/{parteId}', [App\Http\Controllers\ParteProgramaController::class, 'getUsersByParteAndCongregacion'])->name('usuarios-por-parte.get');
        Route::get('/ayudantes-por-encargado/{encargadoId}/{parteId}', [App\Http\Controllers\ParteProgramaController::class, 'getAyudantesByEncargadoAndParte'])->name('ayudantes-por-encargado.get');
        Route::get('/usuarios-disponibles', [App\Http\Controllers\ParteProgramaController::class, 'getUsuariosDisponibles'])->name('usuarios-disponibles.get');
        Route::get('/usuarios-orador-inicial', [App\Http\Controllers\ProgramaController::class, 'getUsuariosOradorInicial'])->name('usuarios-orador-inicial.get');
        Route::get('/usuarios/{usuarioId}/historial-orador', [App\Http\Controllers\ProgramaController::class, 'getHistorialOrador'])->name('usuarios.historial-orador');
        Route::get('/usuarios-presidencia', [App\Http\Controllers\ProgramaController::class, 'getUsuariosPresidencia'])->name('usuarios-presidencia.get');
        Route::get('/usuarios/{usuarioId}/historial-presidencia', [App\Http\Controllers\ProgramaController::class, 'getHistorialPresidencia'])->name('usuarios.historial-presidencia');
        Route::get('/usuarios/{encargadoId}/{parteId}/historial-segunda-seccion', [App\Http\Controllers\UserController::class, 'getHistorialSegundaSeccion'])->name('usuarios.historial-segunda-seccion');
        Route::get('/canciones-disponibles', [App\Http\Controllers\ProgramaController::class, 'getCancionesDisponibles'])->name('canciones-disponibles.get');
        Route::get('/usuarios/{usuarioId}/historial-participaciones', [App\Http\Controllers\ParteProgramaController::class, 'getHistorialParticipaciones'])->name('usuarios.historial-participaciones');
        Route::get('/verificar-sexos-usuarios', [App\Http\Controllers\ParteProgramaController::class, 'verificarSexosUsuarios'])->name('verificar-sexos-usuarios.get');
        Route::get('/verificar-sexo-encargado', [App\Http\Controllers\ParteProgramaController::class, 'verificarSexoEncargado'])->name('verificar-sexo-encargado.get');
        Route::get('/ayudantes-por-parte/{parteId}', [App\Http\Controllers\ParteProgramaController::class, 'getAyudantesByParte'])->name('ayudantes-por-parte.get');
        Route::get('/encargados-por-parte-programa/{parteId}', [App\Http\Controllers\ParteProgramaController::class, 'getEncargadosByPartePrograma'])->name('encargados-por-parte-programa.get');
        Route::get('/encargados-por-parte-programa-smm/{parteId}', [App\Http\Controllers\ParteProgramaController::class, 'getEncargadosByParteProgramaSmm'])->name('encargados-por-parte-programa-smm.get');
        Route::get('/ayudantes-por-parte-programa/{parteId}', [App\Http\Controllers\ParteProgramaController::class, 'getAyudantesByParteProgramaSmm'])->name('ayudantes-por-parte-programa.get');
        Route::get('/usuarios-participantes-programa', [App\Http\Controllers\ParteProgramaController::class, 'getUsuariosParticipantesPrograma'])->name('usuarios-participantes-programa.get');
        Route::post('/partes-programa/{id}/move-up', [App\Http\Controllers\ParteProgramaController::class, 'moveUp'])->name('partes-programa.move-up');
        Route::post('/partes-programa/{id}/move-down', [App\Http\Controllers\ParteProgramaController::class, 'moveDown'])->name('partes-programa.move-down');
    });

    // Rutas de escritura que requieren permisos de administrador (solo perfil 1)
    Route::middleware(['auth', 'admin'])->group(function () {

        // NOTA: Ruta de exportar PDF movida fuera del grupo para evitar conflictos

        // Gesti?n de Perfiles - Escritura
        Route::post('/perfiles', [App\Http\Controllers\PerfilController::class, 'store'])->name('perfiles.store');
        Route::get('/perfiles/{id}/edit', [App\Http\Controllers\PerfilController::class, 'edit'])->name('perfiles.edit');
        Route::put('/perfiles/{id}', [App\Http\Controllers\PerfilController::class, 'update'])->name('perfiles.update');
        Route::delete('/perfiles/{id}', [App\Http\Controllers\PerfilController::class, 'destroy'])->name('perfiles.destroy');

        // Gesti?n de Asignaciones - Escritura
        Route::post('/asignaciones', [App\Http\Controllers\AsignacionController::class, 'store'])->name('asignaciones.store');
        Route::get('/asignaciones/{id}/edit', [App\Http\Controllers\AsignacionController::class, 'edit'])->name('asignaciones.edit');
        Route::put('/asignaciones/{id}', [App\Http\Controllers\AsignacionController::class, 'update'])->name('asignaciones.update');
        Route::delete('/asignaciones/{id}', [App\Http\Controllers\AsignacionController::class, 'destroy'])->name('asignaciones.destroy');

        // Gesti?n de Sexo - Escritura
        Route::post('/sexo', [App\Http\Controllers\SexoController::class, 'store'])->name('sexo.store');
        Route::get('/sexo/{id}/edit', [App\Http\Controllers\SexoController::class, 'edit'])->name('sexo.edit');
        Route::put('/sexo/{id}', [App\Http\Controllers\SexoController::class, 'update'])->name('sexo.update');
        Route::delete('/sexo/{id}', [App\Http\Controllers\SexoController::class, 'destroy'])->name('sexo.destroy');

        // Gesti?n de Nombramiento - Escritura (solo perfil 1)
        Route::post('/nombramiento', [App\Http\Controllers\NombramientoController::class, 'store'])->name('nombramiento.store');
        Route::get('/nombramiento/{id}/edit', [App\Http\Controllers\NombramientoController::class, 'edit'])->name('nombramiento.edit');
        Route::put('/nombramiento/{id}', [App\Http\Controllers\NombramientoController::class, 'update'])->name('nombramiento.update');
        Route::delete('/nombramiento/{id}', [App\Http\Controllers\NombramientoController::class, 'destroy'])->name('nombramiento.destroy');

        // Gesti?n de Esperanza - Solo para administradores (perfil 1)
        Route::get('/esperanza', [App\Http\Controllers\EsperanzaController::class, 'index'])->name('esperanza.index');
        Route::post('/esperanza', [App\Http\Controllers\EsperanzaController::class, 'store'])->name('esperanza.store');
        Route::get('/esperanza/{id}/edit', [App\Http\Controllers\EsperanzaController::class, 'edit'])->name('esperanza.edit');
        Route::put('/esperanza/{id}', [App\Http\Controllers\EsperanzaController::class, 'update'])->name('esperanza.update');
        Route::delete('/esperanza/{id}', [App\Http\Controllers\EsperanzaController::class, 'destroy'])->name('esperanza.destroy');

        // Gesti?n de Servicios - Escritura solo para administradores (perfil 1)
        Route::post('/servicios', [App\Http\Controllers\ServicioController::class, 'store'])->name('servicios.store');
        Route::get('/servicios/{id}/edit', [App\Http\Controllers\ServicioController::class, 'edit'])->name('servicios.edit');
        Route::put('/servicios/{id}', [App\Http\Controllers\ServicioController::class, 'update'])->name('servicios.update');
        Route::delete('/servicios/{id}', [App\Http\Controllers\ServicioController::class, 'destroy'])->name('servicios.destroy');

        // Gesti?n de Congregaciones - Solo para administradores (perfil 1) - ESCRITURA
        Route::get('/congregaciones', [App\Http\Controllers\CongregacionController::class, 'index'])->name('congregaciones.index');
        Route::post('/congregaciones', [App\Http\Controllers\CongregacionController::class, 'store'])->name('congregaciones.store');
        Route::get('/congregaciones/{id}/edit', [App\Http\Controllers\CongregacionController::class, 'edit'])->name('congregaciones.edit');
        Route::put('/congregaciones/{id}', [App\Http\Controllers\CongregacionController::class, 'update'])->name('congregaciones.update');
        Route::delete('/congregaciones/{id}', [App\Http\Controllers\CongregacionController::class, 'destroy'])->name('congregaciones.destroy');

        // Gesti?n de Canciones - Solo para administradores (perfil 1) - ESCRITURA
        Route::get('/canciones', [App\Http\Controllers\CancionController::class, 'index'])->name('canciones.index');
        Route::post('/canciones', [App\Http\Controllers\CancionController::class, 'store'])->name('canciones.store');
        Route::get('/canciones/{cancion}/edit', [App\Http\Controllers\CancionController::class, 'edit'])->name('canciones.edit');
        Route::put('/canciones/{cancion}', [App\Http\Controllers\CancionController::class, 'update'])->name('canciones.update');
        Route::delete('/canciones/{cancion}', [App\Http\Controllers\CancionController::class, 'destroy'])->name('canciones.destroy');

        // Gesti?n de Estados Espirituales - Solo para administradores (perfil 1) - ESCRITURA
        Route::get('/estados-espirituales', [App\Http\Controllers\EstadoEspiritualController::class, 'index'])->name('estados-espirituales.index');
        Route::post('/estados-espirituales', [App\Http\Controllers\EstadoEspiritualController::class, 'store'])->name('estados-espirituales.store');
        Route::get('/estados-espirituales/{id}/edit', [App\Http\Controllers\EstadoEspiritualController::class, 'edit'])->name('estados-espirituales.edit');
        Route::put('/estados-espirituales/{id}', [App\Http\Controllers\EstadoEspiritualController::class, 'update'])->name('estados-espirituales.update');

        // Gestión de Secciones Reunión - Solo para administradores (perfil 1) - ESCRITURA
        Route::get('/secciones-reunion', [App\Http\Controllers\SeccionReunionController::class, 'index'])->name('secciones-reunion.index');
        Route::post('/secciones-reunion', [App\Http\Controllers\SeccionReunionController::class, 'store'])->name('secciones-reunion.store');
        Route::get('/secciones-reunion/{id}/edit', [App\Http\Controllers\SeccionReunionController::class, 'edit'])->name('secciones-reunion.edit');
        Route::put('/secciones-reunion/{id}', [App\Http\Controllers\SeccionReunionController::class, 'update'])->name('secciones-reunion.update');
        Route::delete('/secciones-reunion/{id}', [App\Http\Controllers\SeccionReunionController::class, 'destroy'])->name('secciones-reunion.destroy');

        // Gestión de Partes Sección - Solo para administradores (perfil 1) - ESCRITURA
        Route::get('/partes-seccion', [App\Http\Controllers\ParteSeccionController::class, 'index'])->name('partes-seccion.index');
        Route::post('/partes-seccion', [App\Http\Controllers\ParteSeccionController::class, 'store'])->name('partes-seccion.store');
        Route::get('/partes-seccion/{id}/edit', [App\Http\Controllers\ParteSeccionController::class, 'edit'])->name('partes-seccion.edit');
        Route::put('/partes-seccion/{id}', [App\Http\Controllers\ParteSeccionController::class, 'update'])->name('partes-seccion.update');
        Route::delete('/partes-seccion/{id}', [App\Http\Controllers\ParteSeccionController::class, 'destroy'])->name('partes-seccion.destroy');

        Route::delete('/estados-espirituales/{id}', [App\Http\Controllers\EstadoEspiritualController::class, 'destroy'])->name('estados-espirituales.destroy');
    });

    // Gesti?n de Grupos - Para usuarios con acceso al menú de administración o gestión de personas
    Route::middleware(['auth'])->group(function () {
        // Lectura de grupos (Admin, Supervisor, Coordinator, Subcoordinator, Secretary, Subsecretary, Organizer, Suborganizer)
        Route::get('/grupos', [App\Http\Controllers\GrupoController::class, 'index'])->name('grupos.index');
        Route::get('/grupos/data', [App\Http\Controllers\GrupoController::class, 'getData'])->name('grupos.data');
        Route::get('/grupos/{id}', [App\Http\Controllers\GrupoController::class, 'show'])->name('grupos.show');
        
        // Escritura de grupos (Admin, Coordinator, Secretary, Organizer)
        Route::post('/grupos', [App\Http\Controllers\GrupoController::class, 'store'])->name('grupos.store');
        Route::put('/grupos/{id}', [App\Http\Controllers\GrupoController::class, 'update'])->name('grupos.update');
        Route::delete('/grupos/{id}', [App\Http\Controllers\GrupoController::class, 'destroy'])->name('grupos.destroy');
    });

});
