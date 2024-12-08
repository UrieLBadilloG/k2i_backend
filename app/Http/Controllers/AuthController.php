<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // Método para iniciar sesión
    public function login(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Verificar si el usuario existe
        $user = User::where('email', $request->email)->first();

        // Validar las credenciales del usuario
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        // Crear un token con Sanctum
        $token = $user->createToken('authToken')->plainTextToken;

        // Responder con el token
        return response()->json(['token' => $token], 200);
    }

    // Método para cerrar sesión
    public function logout(Request $request)
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada exitosamente'], 200);
    }

    // Método para obtener información del usuario autenticado
    public function userInfo(Request $request)
    {
        return response()->json($request->user());
    }
    // En AuthController o un UserController
public function createUser(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8',
        'role' => 'required|in:admin,user',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => $request->role,
    ]);

    return response()->json(['message' => 'Usuario creado exitosamente', 'user' => $user], 201);
}

}
