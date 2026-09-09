<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Facultad;
use Illuminate\Database\Seeder;

class NuevasCarrerasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Facultad::count() === 0) {
            echo "⚠️  No hay facultades. Ejecute FacultadSeeder primero.\n";
            return;
        }

        // Obtener facultades (ajusta según tu estructura)
        $facultadIngenieria = Facultad::where('nombre', 'like', '%Ingeniería%')->first();
        $facultadCienciasSalud = Facultad::where('nombre', 'like', '%Ciencias%Salud%')->orWhere('nombre', 'like', '%Medicina%')->first();
        $facultadEconomia = Facultad::where('nombre', 'like', '%Económicas%')->orWhere('nombre', 'like', '%Administración%')->first();
        
        // Si no existen, usar la primera facultad disponible como fallback
        $facultadIngenieria = $facultadIngenieria ?? Facultad::first();
        $facultadCienciasSalud = $facultadCienciasSalud ?? Facultad::skip(1)->first() ?? Facultad::first();
        $facultadEconomia = $facultadEconomia ?? Facultad::skip(2)->first() ?? Facultad::first();

        $carreras = [
            // Ingeniería de Alimentos
            [
                'facultad_id' => $facultadIngenieria->id,
                'codigo' => 'IAL',
                'nombre' => 'Ingeniería de Alimentos',
                'nivel' => 'ingenieria',
                'titulo_otorgado' => 'Ingeniero de Alimentos',
                'duracion_semestres' => 10,
                'creditos_totales' => 200,
                'modalidad' => 'presencial',
                'descripcion' => 'Formación integral en procesamiento, conservación y calidad de alimentos, aplicando principios de ingeniería y ciencias de la alimentación.',
                'coordinador' => 'Dr. Carlos Mendoza',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultadIngenieria->id,
                'codigo' => 'TSU-IAL',
                'nombre' => 'TSU en Ingeniería de Alimentos',
                'nivel' => 'tsu',
                'titulo_otorgado' => 'Técnico Superior Universitario en Ingeniería de Alimentos',
                'duracion_semestres' => 6,
                'creditos_totales' => 120,
                'modalidad' => 'presencial',
                'descripcion' => 'Formación técnica en procesamiento y control de calidad de alimentos.',
                'coordinador' => 'Ing. María González',
                'activo' => true,
            ],

            // Veterinaria
            [
                'facultad_id' => $facultadCienciasSalud->id,
                'codigo' => 'VET',
                'nombre' => 'Medicina Veterinaria',
                'nivel' => 'licenciatura',
                'titulo_otorgado' => 'Médico Veterinario',
                'duracion_semestres' => 10,
                'creditos_totales' => 220,
                'modalidad' => 'presencial',
                'descripcion' => 'Formación en prevención, diagnóstico y tratamiento de enfermedades animales, con énfasis en salud pública y producción pecuaria.',
                'coordinador' => 'Dra. Ana Rodríguez',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultadCienciasSalud->id,
                'codigo' => 'TSU-VET',
                'nombre' => 'TSU en Cuidado y Atención Animal',
                'nivel' => 'tsu',
                'titulo_otorgado' => 'Técnico Superior Universitario en Cuidado y Atención Animal',
                'duracion_semestres' => 6,
                'creditos_totales' => 110,
                'modalidad' => 'presencial',
                'descripcion' => 'Formación técnica en manejo, cuidado básico y atención primaria de animales.',
                'coordinador' => 'Lic. Pedro Martínez',
                'activo' => true,
            ],

            // Ingeniería Industrial
            [
                'facultad_id' => $facultadIngenieria->id,
                'codigo' => 'IIN',
                'nombre' => 'Ingeniería Industrial',
                'nivel' => 'ingenieria',
                'titulo_otorgado' => 'Ingeniero Industrial',
                'duracion_semestres' => 10,
                'creditos_totales' => 205,
                'modalidad' => 'presencial',
                'descripcion' => 'Optimización de procesos productivos, gestión de operaciones y mejora continua en organizaciones industriales y de servicios.',
                'coordinador' => 'Ing. Luis Fernández',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultadIngenieria->id,
                'codigo' => 'TSU-IIN',
                'nombre' => 'TSU en Producción y Supervisión Industrial',
                'nivel' => 'tsu',
                'titulo_otorgado' => 'Técnico Superior Universitario en Producción y Supervisión Industrial',
                'duracion_semestres' => 6,
                'creditos_totales' => 115,
                'modalidad' => 'presencial',
                'descripcion' => 'Formación técnica en supervisión de procesos productivos y control de operaciones industriales.',
                'coordinador' => 'Ing. Roberto Silva',
                'activo' => true,
            ],

            // Ingeniería de Sistemas (actualizar si ya existe)
            [
                'facultad_id' => $facultadIngenieria->id,
                'codigo' => 'TSU-ISI',
                'nombre' => 'TSU en Mantenimiento de Sistemas Informáticos',
                'nivel' => 'tsu',
                'titulo_otorgado' => 'Técnico Superior Universitario en Mantenimiento de Sistemas Informáticos',
                'duracion_semestres' => 6,
                'creditos_totales' => 110,
                'modalidad' => 'presencial',
                'descripcion' => 'Formación técnica en mantenimiento de hardware, software y redes computacionales.',
                'coordinador' => 'Ing. Miguel Herrera',
                'activo' => true,
            ],

            // Enfermería
            [
                'facultad_id' => $facultadCienciasSalud->id,
                'codigo' => 'ENF',
                'nombre' => 'Licenciatura en Enfermería',
                'nivel' => 'licenciatura',
                'titulo_otorgado' => 'Licenciado en Enfermería',
                'duracion_semestres' => 8,
                'creditos_totales' => 180,
                'modalidad' => 'presencial',
                'descripcion' => 'Formación integral en cuidados de enfermería, promoción de la salud y atención a pacientes en diversos contextos.',
                'coordinador' => 'Lic. Carmen López',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultadCienciasSalud->id,
                'codigo' => 'TSU-ENF',
                'nombre' => 'TSU en Enfermería',
                'nivel' => 'tsu',
                'titulo_otorgado' => 'Técnico Superior Universitario en Enfermería',
                'duracion_semestres' => 6,
                'creditos_totales' => 105,
                'modalidad' => 'presencial',
                'descripcion' => 'Formación técnica en cuidados básicos de enfermería y asistencia al personal de salud.',
                'coordinador' => 'Enf. Rosa Díaz',
                'activo' => true,
            ],

            // Administración de Empresas (actualizar si ya existe)
            [
                'facultad_id' => $facultadEconomia->id,
                'codigo' => 'ADM-EMP',
                'nombre' => 'Licenciatura en Administración de Empresas',
                'nivel' => 'licenciatura',
                'titulo_otorgado' => 'Licenciado en Administración de Empresas',
                'duracion_semestres' => 8,
                'creditos_totales' => 165,
                'modalidad' => 'presencial',
                'descripcion' => 'Formación en gestión empresarial, recursos humanos, finanzas y estrategia organizacional.',
                'coordinador' => 'Lic. José Ramírez',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultadEconomia->id,
                'codigo' => 'TSU-ADM',
                'nombre' => 'TSU en Administración de Empresas',
                'nivel' => 'tsu',
                'titulo_otorgado' => 'Técnico Superior Universitario en Administración de Empresas',
                'duracion_semestres' => 6,
                'creditos_totales' => 100,
                'modalidad' => 'presencial',
                'descripcion' => 'Formación técnica en gestión administrativa y apoyo a la dirección empresarial.',
                'coordinador' => 'Lic. Andrea Torres',
                'activo' => true,
            ],

            // Carreras complementarias (extensión solicitada)
            
            // Informática
            [
                'facultad_id' => $facultadIngenieria->id,
                'codigo' => 'LIC-INF',
                'nombre' => 'Licenciatura en Informática',
                'nivel' => 'licenciatura',
                'titulo_otorgado' => 'Licenciado en Informática',
                'duracion_semestres' => 8,
                'creditos_totales' => 170,
                'modalidad' => 'presencial',
                'descripcion' => 'Desarrollo de software, gestión de tecnologías de información y soluciones digitales.',
                'coordinador' => 'Lic. Alberto Sánchez',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultadIngenieria->id,
                'codigo' => 'TSU-INF',
                'nombre' => 'TSU en Informática',
                'nivel' => 'tsu',
                'titulo_otorgado' => 'Técnico Superior Universitario en Informática',
                'duracion_semestres' => 6,
                'creditos_totales' => 105,
                'modalidad' => 'presencial',
                'descripcion' => 'Desarrollo y soporte de aplicaciones informáticas.',
                'coordinador' => 'Ing. Patricia Morales',
                'activo' => true,
            ],

            // Turismo
            [
                'facultad_id' => $facultadEconomia->id,
                'codigo' => 'LIC-TUR',
                'nombre' => 'Licenciatura en Turismo',
                'nivel' => 'licenciatura',
                'titulo_otorgado' => 'Licenciado en Turismo',
                'duracion_semestres' => 8,
                'creditos_totales' => 155,
                'modalidad' => 'presencial',
                'descripcion' => 'Gestión de servicios turísticos, planificación de destinos y emprendimiento en el sector.',
                'coordinador' => 'Lic. Gabriela Rojas',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultadEconomia->id,
                'codigo' => 'TSU-TUR',
                'nombre' => 'TSU en Turismo',
                'nivel' => 'tsu',
                'titulo_otorgado' => 'Técnico Superior Universitario en Turismo',
                'duracion_semestres' => 6,
                'creditos_totales' => 95,
                'modalidad' => 'presencial',
                'descripcion' => 'Operación de servicios turísticos y guía turístico.',
                'coordinador' => 'Lic. Fernando Castro',
                'activo' => true,
            ],

            // Diseño Gráfico
            [
                'facultad_id' => $facultadEconomia->id,
                'codigo' => 'LIC-DG',
                'nombre' => 'Licenciatura en Diseño Gráfico',
                'nivel' => 'licenciatura',
                'titulo_otorgado' => 'Licenciado en Diseño Gráfico',
                'duracion_semestres' => 8,
                'creditos_totales' => 160,
                'modalidad' => 'presencial',
                'descripcion' => 'Comunicación visual, identidad corporativa y diseño multimedia.',
                'coordinador' => 'Dis. Laura Vargas',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultadEconomia->id,
                'codigo' => 'TSU-DG',
                'nombre' => 'TSU en Diseño Gráfico',
                'nivel' => 'tsu',
                'titulo_otorgado' => 'Técnico Superior Universitario en Diseño Gráfico',
                'duracion_semestres' => 6,
                'creditos_totales' => 100,
                'modalidad' => 'presencial',
                'descripcion' => 'Producción de piezas gráficas y asistencia en proyectos de diseño.',
                'coordinador' => 'Dis. Andrés Figueroa',
                'activo' => true,
            ],
        ];

        foreach ($carreras as $carrera) {
            Carrera::updateOrCreate(
                ['codigo' => $carrera['codigo']],
                $carrera
            );
        }

        // Ahora vincular TSU con sus carreras base
        $vinculos = [
            'TSU-IAL' => 'IAL',
            'TSU-VET' => 'VET',
            'TSU-IIN' => 'IIN',
            'TSU-ISI' => 'ISI', // Si existe Ingeniería de Sistemas
            'TSU-ENF' => 'ENF',
            'TSU-ADM' => 'ADM-EMP',
            'TSU-INF' => 'LIC-INF',
            'TSU-TUR' => 'LIC-TUR',
            'TSU-DG' => 'LIC-DG',
        ];

        foreach ($vinculos as $tsuCodigo => $carreraBaseCodigo) {
            $tsu = Carrera::where('codigo', $tsuCodigo)->first();
            $carreraBase = Carrera::where('codigo', $carreraBaseCodigo)->first();
            
            if ($tsu && $carreraBase) {
                $tsu->update(['carrera_base_id' => $carreraBase->id]);
            }
        }

        $this->command->info('✓ Nuevas carreras y TSU agregados exitosamente');
    }
}
