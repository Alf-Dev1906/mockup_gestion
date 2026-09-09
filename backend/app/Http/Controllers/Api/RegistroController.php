<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class RegistroController extends Controller
{
    /**
     * Registro público para aspirantes a estudiantes
     * 
     * Crea:
     * 1. User (role: estudiante)
     * 2. Estudiante (estatus: solicitante, datos mínimos)
     * 3. Retorna token para auto-login
     */
    public function registro(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'cedula' => [
                'required', 
                'string', 
                'regex:/^[VE]-\d{7,8}$/',
                'unique:estudiantes,cedula'
            ],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'cedula.regex' => 'El formato de la cédula debe ser V-12345678 o E-12345678',
            'cedula.unique' => 'Esta cédula ya está registrada en el sistema',
            'email.unique' => 'Este email ya está registrado',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        DB::beginTransaction();

        try {
            // 1. Crear el User
            $user = User::create([
                'name' => trim($request->nombre . ' ' . $request->apellido),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'estudiante',
                'email_verified_at' => now(), // Auto-verificado por simplicidad
            ]);

            // 2. Crear el Estudiante con datos mínimos
            $estudiante = Estudiante::create([
                'cedula' => $request->cedula,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                
                // Campos obligatorios con valores temporales
                'fecha_nacimiento' => now()->subYears(18), // Se actualizará en el wizard
                'matricula' => $this->generarMatriculaTemporal(),
                'fecha_ingreso' => now(),
                'carrera_id' => 1, // Se actualizará en el wizard paso 2
                
                // Estado inicial
                'estatus' => 'inactivo', // Inactivo hasta completar el wizard
            ]);

            DB::commit();

            // 3. Crear token de acceso (8 horas)
            $token = $user->createToken('api-token', ['*'], now()->addMinutes(480));

            return response()->json([
                'message' => 'Registro exitoso. Completa tu solicitud de admisión.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'estudiante' => [
                    'id' => $estudiante->id,
                    'cedula' => $estudiante->cedula,
                    'estatus' => $estudiante->estatus,
                ],
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => 480,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error en registro de estudiante', [
                'email' => $request->email,
                'cedula' => $request->cedula,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['Ocurrió un error al crear la cuenta. Intenta nuevamente.'],
            ]);
        }
    }

    /**
     * Genera una matrícula temporal que luego será reemplazada
     * por una definitiva cuando se apruebe la admisión
     */
    private function generarMatriculaTemporal(): string
    {
        $year = date('Y');
        $random = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        return "TEMP-{$year}-{$random}";
    }

    /**
     * Verifica si un email o cédula ya están registrados
     * Endpoint útil para validación en tiempo real en el frontend
     */
    public function verificarDisponibilidad(Request $request): JsonResponse
    {
        $request->validate([
            'tipo' => ['required', 'in:email,cedula'],
            'valor' => ['required', 'string'],
        ]);

        $disponible = match($request->tipo) {
            'email' => !User::where('email', $request->valor)->exists(),
            'cedula' => !Estudiante::where('cedula', $request->valor)->exists(),
        };

        return response()->json([
            'disponible' => $disponible,
            'mensaje' => $disponible 
                ? 'Disponible' 
                : "Este {$request->tipo} ya está registrado",
        ]);
    }
}
