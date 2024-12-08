<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileUploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas de autenticación
Route::post('/login', [AuthController::class, 'login']); // Inicio de sesión
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']); // Cierre de sesión
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'userInfo']); // Información del usuario

// Rutas protegidas según roles
Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    // Solo administradores pueden subir archivos Excel
    Route::post('/upload-excel', [FileUploadController::class, 'uploadExcel']);
});

Route::middleware(['auth:sanctum', 'isUser'])->group(function () {
    // Los usuarios (consulta y admin) pueden acceder a los datos con paginación
    Route::get('/personas', [FileUploadController::class, 'getPaginatedData']);
    Route::get('/persona/{id}', [FileUploadController::class, 'getPersonaDetails']);
});
Route::middleware('auth:sanctum')->post('/create-user', [AuthController::class, 'createUser'])->middleware('isAdmin');
