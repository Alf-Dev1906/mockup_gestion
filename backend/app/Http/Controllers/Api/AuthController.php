<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Services\SecurityLogService;

class AuthController extends Controller
{
    /**
     * Login — emite un token Sanctum de larga duración.
     * El password NUNCA se devuelve en la respuesta.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        \Log::info('Intento de login', ['email' => $request->email]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            \Log::warning('Usuario no encontrado', ['email' => $request->email]);
            
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            \Log::warning('Password incorrecto', ['email' => $request->email, 'user_id' => $user->id]);
            
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        \Log::info('Login exitoso', ['email' => $request->email, 'user_id' => $user->id]);

        // Revocar tokens anteriores del mismo dispositivo para evitar acumulación
        $user->tokens()->where('name', 'api-token')->delete();

        // Crear token Sanctum — expira en 8 horas (480 min)
        $token = $user->createToken('api-token', ['*'], now()->addMinutes(480));

        return response()->json([
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 480, // minutos
        ]);
    }

    /**
     * Logout — revoca el token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Devuelve el usuario autenticado (sin password ni tokens).
     * Incluye información adicional según el rol.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $response = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ];

        // Agregar información adicional para estudiantes
        // Nota: Las tablas estudiantes y profesores NO tienen user_id
        // Se relacionan por email
        if ($user->role === 'estudiante') {
            $estudiante = \App\Models\Estudiante::where('email', $user->email)->first();
            if ($estudiante) {
                $response['estudiante'] = [
                    'id' => $estudiante->id,
                    'matricula' => $estudiante->matricula,
                    'estatus' => $estudiante->estatus,
                    'carrera_id' => $estudiante->carrera_id,
                    'semestre_actual' => $estudiante->semestre_actual,
                ];
            }
        }

        // Agregar información adicional para profesores
        if ($user->role === 'profesor') {
            $profesor = \App\Models\Profesor::where('email', $user->email)->first();
            if ($profesor) {
                $response['profesor'] = [
                    'id' => $profesor->id,
                    'cedula' => $profesor->cedula,
                    'especialidad' => $profesor->especialidad,
                ];
            }
        }

        return response()->json($response);
    }
}
