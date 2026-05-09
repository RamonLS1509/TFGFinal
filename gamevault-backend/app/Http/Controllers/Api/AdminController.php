<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//Gestiona operaciones de usuarios
class AdminController extends Controller
{
    //Devuelve la lista paginada de todos los usuarios registrados en la plataforma
    public function users(): JsonResponse
    {
        $users = User::withCount(['library', 'wishlist', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($users);
    }

    //Elimina un usuario de la plataforma, comprobando primero que no puede eliminarse a si mismo ni tampoco al admin
    public function deleteUser(Request $request, User $user): JsonResponse
    {
        // El usuario no puede eliminarse a sí mismo
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta.',
            ], 403);
        }

        // El usuario no puede eliminar a otro admin
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'No puedes eliminar a otro administrador.',
            ], 403);
        }
        //Lo elimina
        $user->delete();

        return response()->json(['message' => "Usuario {$user->name} eliminado correctamente."]);
    }
}
