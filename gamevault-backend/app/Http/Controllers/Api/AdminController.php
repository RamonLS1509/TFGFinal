<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users(): JsonResponse
    {
        $users = User::withCount(['library', 'wishlist', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($users);
    }

    public function deleteUser(Request $request, User $user): JsonResponse
    {
        // No puede eliminarse a sí mismo
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta.',
            ], 403);
        }

        // No puede eliminar a otro admin
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'No puedes eliminar a otro administrador.',
            ], 403);
        }

        $user->delete();

        return response()->json(['message' => "Usuario {$user->name} eliminado correctamente."]);
    }
}
