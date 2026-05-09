<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//Gestiona todo el sistema de autenticación de la aplicación
class AuthController extends Controller
{
    //Crea un nuevo usuario en la base de datos recibiendo un RegisterRequest
    public function register(RegisterRequest $request): JsonResponse
    {
        //Crea el usuario en la BBDD
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        //Cuando se crea el usuario, automaticamente se logea
        Auth::login($user);

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'user' => $user,
        ], 201);
    }

    //Inicia sesion con email y contraseña
    public function login(LoginRequest $request): JsonResponse
    {
        //Busca al usuario por correo y contraseña y si no coinciden, error 401
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        // Comprueba si hay una sesión disponible antes de generarla. Una vez comprobado genera un nuevo ID de sesión
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'message' => 'Sesión iniciada.',
            'user' => Auth::user(),
        ]);
    }

    //Cierra la sesión del usuario
    public function logout(Request $request): JsonResponse
    {
        //Cierra la sesión con el guard web usado por Sanctum SPA con cookies
        Auth::guard('web')->logout();

        //Elimina la sesión actual
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    //Devuelve los datos del usuario que está autenticado
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
