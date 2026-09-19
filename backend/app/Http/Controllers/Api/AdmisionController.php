<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolicitudAdmision;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdmisionController extends Controller
{
    /**
     * Obtener la solicitud de admisión del estudiante autenticado
     */
    public function obtenerSolicitud()
    {
        $user = Auth::user();
        
        // Obtener el estudiante asociado al usuario
        $estudiante = Estudiante::where('email', $user->email)->first();
        
        if (!$estudiante) {
            return response()->json([
                'message' => 'No se encontró el perfil de estudiante'
            ], 404);
        }
        
        // Obtener o crear solicitud en estado borrador
        $solicitud = SolicitudAdmision::firstOrCreate(
            ['estudiante_id' => $estudiante->id],
            [
                'numero_referencia' => 'SOL-' . date('Y') . '-' . str_pad($estudiante->id, 5, '0', STR_PAD_LEFT),
                'estado' => 'borrador',
                'paso_actual' => 1,
                'paso1_completado' => false,
                'paso2_completado' => false,
                'paso3_completado' => false,
                'paso4_completado' => false,
                'paso5_completado' => false,
            ]
        );
        
        // Cargar relaciones
        $solicitud->load(['estudiante.user', 'carrera', 'carreraAlternativa']);
        
        return response()->json([
            'solicitud' => $solicitud,
            'estudiante' => $estudiante,
            'user' => $user
        ]);
    }
    
    /**
     * Guardar Paso 1: Información Personal
     */
    public function guardarPaso1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_nacimiento' => 'required|date|before:today',
            'genero' => 'required|in:M,F,Otro',
            'telefono_principal' => 'required|string|max:20',
            'telefono_secundario' => 'nullable|string|max:20',
            'direccion' => 'required|string|max:500',
            'ciudad' => 'required|string|max:100',
            'estado' => 'required|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'contacto_emergencia_nombre' => 'required|string|max:150',
            'contacto_emergencia_telefono' => 'required|string|max:20',
            'contacto_emergencia_parentesco' => 'required|string|max:50',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();
        
        if (!$estudiante) {
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }
        
        $solicitud = SolicitudAdmision::where('estudiante_id', $estudiante->id)->first();
        
        if (!$solicitud) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }
        
        // Actualizar datos del paso 1
        $solicitud->update([
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'genero' => $request->genero,
            'telefono' => $request->telefono_principal,
            'telefono_alternativo' => $request->telefono_secundario,
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'estado' => $request->estado,
            'codigo_postal' => $request->codigo_postal,
            'contacto_emergencia_nombre' => $request->contacto_emergencia_nombre,
            'contacto_emergencia_telefono' => $request->contacto_emergencia_telefono,
            'contacto_emergencia_relacion' => $request->contacto_emergencia_parentesco,
        ]);
        
        // Completar paso 1
        $solicitud->completarPaso(1);
        
        return response()->json([
            'message' => 'Paso 1 guardado exitosamente',
            'solicitud' => $solicitud->fresh()
        ]);
    }
    
    /**
     * Guardar Paso 2: Selección de Carrera
     */
    public function guardarPaso2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'carrera_id' => 'required|exists:carreras,id',
            'carrera_alternativa_id' => 'nullable|exists:carreras,id|different:carrera_id',
            'modalidad_preferida' => 'required|in:presencial,semipresencial,distancia',
            'turno_preferido' => 'required|in:mañana,tarde,noche',
            'motivacion' => 'required|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();
        
        if (!$estudiante) {
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }
        
        $solicitud = SolicitudAdmision::where('estudiante_id', $estudiante->id)->first();
        
        if (!$solicitud) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }
        
        // Verificar que puede acceder al paso 2
        if (!$solicitud->puedeAccederPaso(2)) {
            return response()->json([
                'message' => 'Debes completar el paso anterior primero'
            ], 403);
        }
        
        // Actualizar datos del paso 2
        $solicitud->update([
            'carrera_id' => $request->carrera_id,
            'carrera_alternativa_id' => $request->carrera_alternativa_id,
            'modalidad' => $request->modalidad_preferida,
            'turno_preferido' => $request->turno_preferido,
            'motivacion' => $request->motivacion,
        ]);
        
        // Completar paso 2
        $solicitud->completarPaso(2);
        
        return response()->json([
            'message' => 'Paso 2 guardado exitosamente',
            'solicitud' => $solicitud->fresh()
        ]);
    }
    
    /**
     * Guardar Paso 3: Datos Académicos
     */
    public function guardarPaso3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nivel_educativo' => 'required|in:bachiller,tsu,universitario_incompleto,licenciatura,posgrado',
            'institucion_egreso' => 'required|string|max:200',
            'año_graduacion' => 'required|integer|min:1950|max:' . date('Y'),
            'promedio_notas' => 'nullable|numeric|min:0|max:20',
            'tipo_bachillerato' => 'nullable|string|in:ciencias,humanidades,tecnico',
            'mencion' => 'nullable|string|max:100',
            'universidad_procedencia' => 'nullable|string|max:200',
            'carrera_previa' => 'nullable|string|max:200',
            'semestres_completados' => 'nullable|integer|min:1|max:12',
            'desea_convalidar' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();
        
        if (!$estudiante) {
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }
        
        $solicitud = SolicitudAdmision::where('estudiante_id', $estudiante->id)->first();
        
        if (!$solicitud) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }
        
        // Verificar que puede acceder al paso 3
        if (!$solicitud->puedeAccederPaso(3)) {
            return response()->json([
                'message' => 'Debes completar los pasos anteriores primero'
            ], 403);
        }
        
        // Actualizar datos del paso 3
        $solicitud->update([
            'nivel_educativo' => $request->nivel_educativo,
            'institucion_egreso' => $request->institucion_egreso,
            'año_graduacion' => $request->año_graduacion,
            'promedio_notas' => $request->promedio_notas,
            'tipo_bachillerato' => $request->tipo_bachillerato,
            'mencion' => $request->mencion,
            'universidad_procedencia' => $request->universidad_procedencia,
            'carrera_previa' => $request->carrera_previa,
            'semestres_completados' => $request->semestres_completados,
            'desea_convalidar' => $request->desea_convalidar ?? false,
        ]);
        
        // Completar paso 3
        $solicitud->completarPaso(3);
        
        return response()->json([
            'message' => 'Paso 3 guardado exitosamente',
            'solicitud' => $solicitud->fresh()
        ]);
    }
    
    /**
     * Guardar Paso 4: Documentos
     */
    public function guardarPaso4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doc_cedula' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_partida_nacimiento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_titulo_bachiller' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_foto_carnet' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'doc_comprobante_domicilio' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_certificado_medico' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_carta_conducta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();
        
        if (!$estudiante) {
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }
        
        $solicitud = SolicitudAdmision::where('estudiante_id', $estudiante->id)->first();
        
        if (!$solicitud) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }
        
        // Verificar que puede acceder al paso 4
        if (!$solicitud->puedeAccederPaso(4)) {
            return response()->json([
                'message' => 'Debes completar los pasos anteriores primero'
            ], 403);
        }
        
        // Procesar archivos
        $documentos = [
            'doc_cedula',
            'doc_partida_nacimiento',
            'doc_titulo_bachiller',
            'doc_foto_carnet',
            'doc_comprobante_domicilio',
            'doc_certificado_medico',
            'doc_carta_conducta'
        ];
        
        $actualizaciones = [];
        
        foreach ($documentos as $documento) {
            if ($request->hasFile($documento)) {
                // Eliminar archivo anterior si existe
                if ($solicitud->$documento) {
                    Storage::disk('public')->delete($solicitud->$documento);
                }
                
                // Guardar nuevo archivo
                $path = $request->file($documento)->store('admision/documentos', 'public');
                $actualizaciones[$documento] = $path;
            }
        }
        
        // Actualizar campos en la solicitud
        if (!empty($actualizaciones)) {
            $solicitud->update($actualizaciones);
        }
        
        // Verificar que todos los documentos obligatorios estén cargados
        $obligatorios = [
            'doc_cedula',
            'doc_partida_nacimiento',
            'doc_titulo_bachiller',
            'doc_foto_carnet',
            'doc_comprobante_domicilio'
        ];
        
        $todosCompletos = true;
        foreach ($obligatorios as $doc) {
            if (empty($solicitud->$doc)) {
                $todosCompletos = false;
                break;
            }
        }
        
        // Solo completar el paso si todos los obligatorios están
        if ($todosCompletos) {
            $solicitud->completarPaso(4);
        }
        
        return response()->json([
            'message' => $todosCompletos 
                ? 'Documentos guardados exitosamente' 
                : 'Documentos guardados. Completa todos los obligatorios para continuar',
            'solicitud' => $solicitud->fresh(),
            'documentos_completos' => $todosCompletos
        ]);
    }
    
    /**
     * Enviar solicitud (Paso 5)
     */
    public function enviarSolicitud(Request $request)
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('user_id', $user->id)->first();
        
        if (!$estudiante) {
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }
        
        $solicitud = SolicitudAdmision::where('estudiante_id', $estudiante->id)->first();
        
        if (!$solicitud) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }
        
        // Verificar que todos los pasos anteriores estén completos
        if (!$solicitud->paso1_completado || 
            !$solicitud->paso2_completado || 
            !$solicitud->paso3_completado || 
            !$solicitud->paso4_completado) {
            return response()->json([
                'message' => 'Debes completar todos los pasos anteriores antes de enviar'
            ], 403);
        }
        
        // Cambiar estado a "pendiente" y completar paso 5
        $solicitud->update([
            'estado' => 'pendiente',
            'paso5_completado' => true,
            'fecha_envio' => now(),
        ]);
        
        // Actualizar el estatus del estudiante
        $estudiante->update([
            'estatus' => 'pendiente'
        ]);
        
        return response()->json([
            'message' => 'Solicitud enviada exitosamente. Recibirás notificaciones sobre el estado de tu solicitud.',
            'solicitud' => $solicitud->fresh()
        ]);
    }
}
