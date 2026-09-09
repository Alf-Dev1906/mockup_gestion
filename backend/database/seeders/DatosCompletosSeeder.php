<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosCompletosSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Iniciando población masiva de datos...');

        // 1. Facultades
        $this->command->info('📚 Creando facultades...');
        $facultades = [
            ['codigo' => 'FIN', 'nombre' => 'Facultad de Ingeniería', 'decano' => 'Dr. Carlos Pérez', 'telefono' => '0212-5551001', 'email' => 'ingenieria@universidad.edu.ve', 'descripcion' => 'Facultad dedicada a la formación de ingenieros en diversas especialidades', 'activo' => true],
            ['codigo' => 'FCS', 'nombre' => 'Facultad de Ciencias de la Salud', 'decano' => 'Dra. María González', 'telefono' => '0212-5551002', 'email' => 'salud@universidad.edu.ve', 'descripcion' => 'Formación integral en ciencias de la salud', 'activo' => true],
            ['codigo' => 'FCE', 'nombre' => 'Facultad de Ciencias Económicas y Sociales', 'decano' => 'Dr. José Rodríguez', 'telefono' => '0212-5551003', 'email' => 'economia@universidad.edu.ve', 'descripcion' => 'Formación de profesionales en economía, administración y contaduría', 'activo' => true],
            ['codigo' => 'FHU', 'nombre' => 'Facultad de Humanidades y Educación', 'decano' => 'Dra. Ana Martínez', 'telefono' => '0212-5551004', 'email' => 'humanidades@universidad.edu.ve', 'descripcion' => 'Formación humanística y educativa', 'activo' => true],
            ['codigo' => 'FCJ', 'nombre' => 'Facultad de Ciencias Jurídicas y Políticas', 'decano' => 'Dr. Luis Fernández', 'telefono' => '0212-5551005', 'email' => 'derecho@universidad.edu.ve', 'descripcion' => 'Formación de abogados y profesionales del derecho', 'activo' => true],
            ['codigo' => 'FCV', 'nombre' => 'Facultad de Ciencias Veterinarias', 'decano' => 'Dr. Roberto Silva', 'telefono' => '0212-5551006', 'email' => 'veterinaria@universidad.edu.ve', 'descripcion' => 'Formación de médicos veterinarios', 'activo' => true],
            ['codigo' => 'FAR', 'nombre' => 'Facultad de Arquitectura y Diseño', 'decano' => 'Arq. Carmen Ruiz', 'telefono' => '0212-5551007', 'email' => 'arquitectura@universidad.edu.ve', 'descripcion' => 'Formación de arquitectos y diseñadores', 'activo' => true],
        ];

        foreach ($facultades as $f) {
            DB::table('facultades')->insertOrIgnore(array_merge($f, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 2. Carreras (basadas en la vista)
        $this->command->info('🎓 Creando carreras completas...');
        $carreras = [
            // INGENIERÍAS
            ['facultad_id' => 1, 'codigo' => 'ISI', 'nombre' => 'Ingeniería de Sistemas', 'titulo_otorgado' => 'Ingeniero de Sistemas', 'duracion_semestres' => 10, 'creditos_totales' => 180, 'modalidad' => 'presencial', 'nivel' => 'ingenieria', 'activo' => true],
            ['facultad_id' => 1, 'codigo' => 'IEL', 'nombre' => 'Ingeniería Electrónica', 'titulo_otorgado' => 'Ingeniero Electrónico', 'duracion_semestres' => 10, 'creditos_totales' => 185, 'modalidad' => 'presencial', 'nivel' => 'ingenieria', 'activo' => true],
            ['facultad_id' => 1, 'codigo' => 'ICN', 'nombre' => 'Ingeniería Civil', 'titulo_otorgado' => 'Ingeniero Civil', 'duracion_semestres' => 10, 'creditos_totales' => 190, 'modalidad' => 'presencial', 'nivel' => 'ingenieria', 'activo' => true],
            ['facultad_id' => 1, 'codigo' => 'IIN', 'nombre' => 'Ingeniería Industrial', 'titulo_otorgado' => 'Ingeniero Industrial', 'duracion_semestres' => 10, 'creditos_totales' => 180, 'modalidad' => 'presencial', 'nivel' => 'ingenieria', 'activo' => true],
            ['facultad_id' => 1, 'codigo' => 'IMC', 'nombre' => 'Ingeniería Mecánica', 'titulo_otorgado' => 'Ingeniero Mecánico', 'duracion_semestres' => 10, 'creditos_totales' => 185, 'modalidad' => 'presencial', 'nivel' => 'ingenieria', 'activo' => true],
            ['facultad_id' => 6, 'codigo' => 'IAL', 'nombre' => 'Ingeniería de Alimentos', 'titulo_otorgado' => 'Ingeniero de Alimentos', 'duracion_semestres' => 10, 'creditos_totales' => 180, 'modalidad' => 'presencial', 'nivel' => 'ingenieria', 'activo' => true],
            
            // LICENCIATURAS
            ['facultad_id' => 2, 'codigo' => 'MED', 'nombre' => 'Medicina', 'titulo_otorgado' => 'Médico Cirujano', 'duracion_semestres' => 12, 'creditos_totales' => 240, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 2, 'codigo' => 'ENF', 'nombre' => 'Enfermería', 'titulo_otorgado' => 'Licenciado en Enfermería', 'duracion_semestres' => 8, 'creditos_totales' => 160, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 3, 'codigo' => 'ADM', 'nombre' => 'Administración de Empresas', 'titulo_otorgado' => 'Licenciado en Administración', 'duracion_semestres' => 8, 'creditos_totales' => 150, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 3, 'codigo' => 'CNT', 'nombre' => 'Contaduría Pública', 'titulo_otorgado' => 'Licenciado en Contaduría', 'duracion_semestres' => 8, 'creditos_totales' => 155, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 3, 'codigo' => 'ECO', 'nombre' => 'Economía', 'titulo_otorgado' => 'Licenciado en Economía', 'duracion_semestres' => 8, 'creditos_totales' => 150, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 4, 'codigo' => 'PSI', 'nombre' => 'Psicología', 'titulo_otorgado' => 'Licenciado en Psicología', 'duracion_semestres' => 10, 'creditos_totales' => 170, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 4, 'codigo' => 'EDU', 'nombre' => 'Educación', 'titulo_otorgado' => 'Licenciado en Educación', 'duracion_semestres' => 8, 'creditos_totales' => 150, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 4, 'codigo' => 'COM', 'nombre' => 'Comunicación Social', 'titulo_otorgado' => 'Licenciado en Comunicación Social', 'duracion_semestres' => 8, 'creditos_totales' => 145, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 5, 'codigo' => 'DER', 'nombre' => 'Derecho', 'titulo_otorgado' => 'Abogado', 'duracion_semestres' => 10, 'creditos_totales' => 180, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 6, 'codigo' => 'VET', 'nombre' => 'Medicina Veterinaria', 'titulo_otorgado' => 'Médico Veterinario', 'duracion_semestres' => 10, 'creditos_totales' => 200, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 7, 'codigo' => 'ARQ', 'nombre' => 'Arquitectura', 'titulo_otorgado' => 'Arquitecto', 'duracion_semestres' => 10, 'creditos_totales' => 190, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 7, 'codigo' => 'DIS', 'nombre' => 'Diseño Gráfico', 'titulo_otorgado' => 'Licenciado en Diseño Gráfico', 'duracion_semestres' => 8, 'creditos_totales' => 140, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            ['facultad_id' => 2, 'codigo' => 'BIO', 'nombre' => 'Biología', 'titulo_otorgado' => 'Licenciado en Biología', 'duracion_semestres' => 8, 'creditos_totales' => 155, 'modalidad' => 'presencial', 'nivel' => 'licenciatura', 'activo' => true],
            
            // TSU
            ['facultad_id' => 1, 'codigo' => 'TSISI', 'nombre' => 'TSU en Informática', 'titulo_otorgado' => 'Técnico Superior Universitario en Informática', 'duracion_semestres' => 6, 'creditos_totales' => 90, 'modalidad' => 'presencial', 'nivel' => 'tsu', 'activo' => true],
            ['facultad_id' => 3, 'codigo' => 'TSADM', 'nombre' => 'TSU en Administración', 'titulo_otorgado' => 'Técnico Superior Universitario en Administración', 'duracion_semestres' => 6, 'creditos_totales' => 85, 'modalidad' => 'presencial', 'nivel' => 'tsu', 'activo' => true],
            ['facultad_id' => 3, 'codigo' => 'TSCNT', 'nombre' => 'TSU en Contabilidad', 'titulo_otorgado' => 'Técnico Superior Universitario en Contabilidad', 'duracion_semestres' => 6, 'creditos_totales' => 85, 'modalidad' => 'presencial', 'nivel' => 'tsu', 'activo' => true],
            ['facultad_id' => 2, 'codigo' => 'TSENF', 'nombre' => 'TSU en Enfermería', 'titulo_otorgado' => 'Técnico Superior Universitario en Enfermería', 'duracion_semestres' => 6, 'creditos_totales' => 95, 'modalidad' => 'presencial', 'nivel' => 'tsu', 'activo' => true],
            ['facultad_id' => 3, 'codigo' => 'TSTUR', 'nombre' => 'TSU en Turismo', 'titulo_otorgado' => 'Técnico Superior Universitario en Turismo', 'duracion_semestres' => 6, 'creditos_totales' => 80, 'modalidad' => 'presencial', 'nivel' => 'tsu', 'activo' => true],
        ];

        foreach ($carreras as $c) {
            DB::table('carreras')->insertOrIgnore(array_merge($c, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 3. Aulas (50 aulas)
        $this->command->info('🏫 Creando 50 aulas...');
        for ($i = 1; $i <= 50; $i++) {
            $edificio = ['A', 'B', 'C', 'D', 'E', 'F', 'G'][($i - 1) % 7];
            $facultadId = (($i - 1) % 7) + 1;
            $tipo = $i <= 15 ? 'laboratorio' : 'aula_regular';
            
            DB::table('aulas')->insertOrIgnore([
                'facultad_id' => $facultadId,
                'codigo' => $edificio . str_pad($i, 2, '0', STR_PAD_LEFT),
                'nombre' => ($tipo === 'laboratorio' ? 'Laboratorio ' : 'Aula ') . $i,
                'capacidad' => rand(25, 45),
                'tipo' => $tipo,
                'edificio' => 'Edificio ' . $edificio,
                'piso' => ceil(($i % 10) / 3) + 1,
                'estatus' => 'disponible',
                'tiene_proyector' => $i % 2 == 0,
                'tiene_aire_acondicionado' => $i % 3 == 0,
                'tiene_computadoras' => $tipo === 'laboratorio',
                'numero_computadoras' => $tipo === 'laboratorio' ? rand(20, 35) : 0,
                'accesible_discapacitados' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Profesores (25)
        $this->command->info('👨‍🏫 Creando 25 profesores...');
        $nombres = ['Pedro', 'Ana', 'Carlos', 'María', 'José', 'Laura', 'Luis', 'Carmen', 'Jorge', 'Rosa', 'Miguel', 'Patricia', 'Antonio', 'Elena', 'Francisco', 'Isabel', 'Manuel', 'Teresa', 'Ramón', 'Lucía', 'Alberto', 'Marta', 'Rafael', 'Silvia', 'Javier'];
        $apellidos = ['García', 'Rodríguez', 'Martínez', 'López', 'González', 'Pérez', 'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Gómez', 'Díaz', 'Cruz', 'Hernández', 'Morales', 'Jiménez', 'Ruiz', 'Álvarez', 'Romero', 'Vargas', 'Castro', 'Ortiz', 'Mendoza', 'Silva'];
        $especialidades = ['Matemáticas', 'Física', 'Química', 'Programación', 'Base de Datos', 'Redes', 'Ingeniería de Software', 'Anatomía', 'Fisiología', 'Farmacología', 'Contabilidad', 'Finanzas', 'Marketing', 'Recursos Humanos', 'Psicología Clínica', 'Pedagogía', 'Derecho Civil', 'Arquitectura', 'Diseño', 'Veterinaria'];
        $titulos = ['Licenciado', 'Magister', 'Doctor', 'PhD', 'MBA'];

        for ($i = 0; $i < 25; $i++) {
            $facultadId = ($i % 7) + 1;
            
            DB::table('profesores')->insertOrIgnore([
                'facultad_id' => $facultadId,
                'cedula' => 'V-' . str_pad(10000000 + $i, 8, '0', STR_PAD_LEFT),
                'codigo_empleado' => 'PROF-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'nombre' => $nombres[$i],
                'apellido' => $apellidos[$i],
                'email' => strtolower($nombres[$i] . '.' . $apellidos[$i]) . '@universidad.edu.ve',
                'telefono' => '0424-' . rand(1000000, 9999999),
                'fecha_nacimiento' => date('Y-m-d', strtotime('-' . rand(35, 60) . ' years')),
                'genero' => $i % 2 == 0 ? 'M' : 'F',
                'especialidad' => $especialidades[$i % count($especialidades)],
                'titulo_academico' => $titulos[$i % count($titulos)],
                'fecha_contratacion' => date('Y-m-d', strtotime('-' . rand(1, 15) . ' years')),
                'tipo_contrato' => $i % 4 == 0 ? 'medio_tiempo' : 'tiempo_completo',
                'categoria' => ['instructor', 'asistente', 'agregado', 'asociado', 'titular'][$i % 5],
                'horas_semanales' => $i % 4 == 0 ? 20 : 40,
                'estatus' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Estudiantes (100)
        $this->command->info('👨‍🎓 Creando 100 estudiantes...');
        $nombresEst = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Carmen', 'Pedro', 'Rosa', 'Jorge', 'Laura', 'Miguel', 'Patricia', 'Antonio', 'Elena', 'José', 'Isabel', 'Francisco', 'Teresa', 'Manuel', 'Lucía'];
        $apellidosEst = ['Pérez', 'García', 'Rodríguez', 'López', 'Martínez', 'González', 'Hernández', 'Díaz', 'Moreno', 'Muñoz', 'Álvarez', 'Romero', 'Gutiérrez', 'Alonso', 'Navarro', 'Torres', 'Domínguez', 'Gil', 'Vázquez', 'Serrano'];
        $ciudades = ['Caracas', 'Valencia', 'Maracaibo', 'Barquisimeto', 'Maracay', 'Barcelona', 'Maturín', 'San Cristóbal'];
        $estados = ['Distrito Capital', 'Carabobo', 'Zulia', 'Lara', 'Aragua', 'Anzoátegui', 'Monagas', 'Táchira'];

        for ($i = 1; $i <= 100; $i++) {
            $nombre = $nombresEst[array_rand($nombresEst)];
            $apellido = $apellidosEst[array_rand($apellidosEst)];
            $ciudadIdx = array_rand($ciudades);
            $carreraId = rand(1, 24);
            $año = date('Y') - rand(0, 4);
            $semestre = rand(1, 10);
            
            DB::table('estudiantes')->insertOrIgnore([
                'carrera_id' => $carreraId,
                'cedula' => 'V-' . str_pad(20000000 + $i, 8, '0', STR_PAD_LEFT),
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => strtolower($nombre . '.' . $apellido . $i) . '@est.universidad.edu.ve',
                'telefono' => '0424-' . rand(1000000, 9999999),
                'fecha_nacimiento' => date('Y-m-d', strtotime('-' . rand(18, 25) . ' years')),
                'genero' => $i % 2 == 0 ? 'M' : 'F',
                'direccion' => 'Calle ' . rand(1, 50) . ', ' . $ciudades[$ciudadIdx],
                'ciudad' => $ciudades[$ciudadIdx],
                'estado' => $estados[$ciudadIdx],
                'matricula' => $año . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'fecha_ingreso' => date('Y-m-d', strtotime('-' . rand(0, 4) . ' years')),
                'semestre_actual' => $semestre,
                'indice_academico' => rand(1200, 2000) / 100,
                'creditos_aprobados' => ($semestre - 1) * rand(15, 18),
                'estatus' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 6. Materias (5 por carrera)
        $this->command->info('📖 Creando materias...');
        $materiasBase = [
            ['nombre' => 'Matemáticas', 'creditos' => 4, 'tipo' => 'obligatoria', 'horas_teoricas' => 4, 'horas_practicas' => 0],
            ['nombre' => 'Física', 'creditos' => 4, 'tipo' => 'obligatoria', 'horas_teoricas' => 3, 'horas_practicas' => 2],
            ['nombre' => 'Química', 'creditos' => 4, 'tipo' => 'obligatoria', 'horas_teoricas' => 3, 'horas_practicas' => 2],
            ['nombre' => 'Introducción a la Carrera', 'creditos' => 3, 'tipo' => 'obligatoria', 'horas_teoricas' => 3, 'horas_practicas' => 0],
            ['nombre' => 'Metodología de la Investigación', 'creditos' => 3, 'tipo' => 'obligatoria', 'horas_teoricas' => 2, 'horas_practicas' => 1],
        ];

        $contador = 1;
        for ($carreraId = 1; $carreraId <= 24; $carreraId++) {
            foreach ($materiasBase as $mat) {
                DB::table('materias')->insertOrIgnore([
                    'carrera_id' => $carreraId,
                    'codigo' => 'MAT' . str_pad($contador++, 4, '0', STR_PAD_LEFT),
                    'nombre' => $mat['nombre'],
                    'creditos' => $mat['creditos'],
                    'horas_teoricas' => $mat['horas_teoricas'],
                    'horas_practicas' => $mat['horas_practicas'],
                    'horas_laboratorio' => 0,
                    'semestre_recomendado' => 1,
                    'tipo' => $mat['tipo'],
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 7. Horarios (para 60 materias)
        $this->command->info('📅 Creando horarios...');
        $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $horasInicio = ['08:00:00', '09:30:00', '11:00:00', '14:00:00', '15:30:00', '17:00:00', '18:30:00'];
        $horasFin = ['09:20:00', '10:50:00', '12:20:00', '15:20:00', '16:50:00', '18:20:00', '19:50:00'];
        $secciones = ['A', 'B', 'C', 'D', '01', '02', '03', '04'];
        
        for ($i = 1; $i <= 60; $i++) { // Horarios para 60 materias
            $materiaId = $i; // Cada horario para una materia diferente
            $profesorId = rand(1, 25);
            $aulaId = rand(1, 50);
            $dia = $diasSemana[array_rand($diasSemana)];
            $horaIdx = array_rand($horasInicio);
            $periodoAcademico = date('Y') . '-1';
            $anno = date('Y');
            $periodo = 1;
            $cupoMaximo = rand(25, 45);
            $cupoActual = rand(15, $cupoMaximo);
            
            DB::table('horarios')->insertOrIgnore([
                'materia_id' => $materiaId,
                'profesor_id' => $profesorId,
                'aula_id' => $aulaId,
                'seccion' => $secciones[array_rand($secciones)],
                'periodo_academico' => $periodoAcademico,
                'anno' => $anno,
                'periodo' => $periodo,
                'dia_semana' => $dia,
                'hora_inicio' => $horasInicio[$horaIdx],
                'hora_fin' => $horasFin[$horaIdx],
                'cupo_maximo' => $cupoMaximo,
                'cupo_actual' => $cupoActual,
                'lista_espera' => rand(0, 5),
                'estatus' => ['abierto', 'en_curso'][rand(0, 1)],
                'fecha_inicio' => date('Y-m-d', strtotime('-1 month')),
                'fecha_fin' => date('Y-m-d', strtotime('+5 months')),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 8. Inscripciones (4 materias por estudiante en promedio)
        $this->command->info('📝 Creando inscripciones...');
        
        // Obtener IDs de horarios
        $horarios = DB::table('horarios')->pluck('id')->toArray();
        
        for ($estudianteId = 1; $estudianteId <= 100; $estudianteId++) {
            $numInscripciones = rand(3, 6); // Cada estudiante se inscribe en 3-6 materias
            
            for ($j = 0; $j < $numInscripciones; $j++) {
                if (empty($horarios)) {
                    break;
                }
                $horarioId = $horarios[array_rand($horarios)];
                $estatus = rand(0, 10) > 2 ? 'inscrito' : 'retirado'; // 80% inscritos, 20% retirados
                $periodoAcademico = date('Y') . '-1';
                
                DB::table('inscripciones')->insertOrIgnore([
                    'estudiante_id' => $estudianteId,
                    'horario_id' => $horarioId,
                    'periodo_academico' => $periodoAcademico,
                    'fecha_inscripcion' => date('Y-m-d', strtotime('-' . rand(30, 180) . ' days')),
                    'estatus' => $estatus,
                    'nota_parcial_1' => $estatus === 'inscrito' ? rand(800, 2000) / 100 : null,
                    'nota_parcial_2' => $estatus === 'inscrito' ? rand(800, 2000) / 100 : null,
                    'nota_final' => $estatus === 'inscrito' ? rand(800, 2000) / 100 : null,
                    'inasistencias' => rand(0, 8),
                    'porcentaje_asistencia' => rand(8000, 10000) / 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 9. Calificaciones (para algunas inscripciones)
        $this->command->info('📊 Creando calificaciones...');
        $inscripciones = DB::table('inscripciones')->where('estatus', 'inscrito')->take(200)->get();
        
        foreach ($inscripciones as $inscripcion) {
            if (rand(0, 10) > 3) { // 70% tienen calificaciones
                $notaFinal = rand(800, 2000) / 100;
                $estatus = $notaFinal >= 10 ? 'aprobado' : 'reprobado';
                
                DB::table('calificaciones')->insertOrIgnore([
                    'inscripcion_id' => $inscripcion->id,
                    'nota_corte_1' => rand(800, 2000) / 100,
                    'nota_corte_2' => rand(800, 2000) / 100,
                    'nota_corte_3' => rand(800, 2000) / 100,
                    'nota_final' => $notaFinal,
                    'estatus' => $estatus,
                    'asistencias' => rand(70, 100),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('');
        $this->command->info('✅ ¡Datos completos creados exitosamente!');
        $this->command->line('');
        $this->command->line('📊 Resumen de datos poblados:');
        $this->command->line('   - 7 Facultades');
        $this->command->line('   - 24 Carreras (6 Ingenierías, 13 Licenciaturas, 5 TSU)');
        $this->command->line('   - 50 Aulas');
        $this->command->line('   - 25 Profesores');
        $this->command->line('   - 100 Estudiantes');
        $this->command->line('   - 120 Materias');
        $this->command->line('   - 60 Horarios');
        $this->command->line('   - ~400 Inscripciones');
        $this->command->line('   - ~280 Calificaciones');
    }
}