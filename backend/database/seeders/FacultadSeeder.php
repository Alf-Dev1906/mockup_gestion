<?php

namespace Database\Seeders;

use App\Models\Facultad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncar tabla para evitar duplicados
        DB::table('facultades')->truncate();

        echo "Creando facultades...\n";

        $facultades = [
            [
                'codigo' => 'ING',
                'nombre' => 'Facultad de Ingeniería',
                'decano' => 'Dr. Carlos Rodríguez',
                'email' => 'ing@universidad.edu.ve',
                'telefono' => '0212-5551001',
                'descripcion' => 'La Facultad de Ingeniería ofrece programas de alta calidad en diversas ramas de la ingeniería.',
                'activo' => true,
            ],
            [
                'codigo' => 'CIE',
                'nombre' => 'Facultad de Ciencias',
                'decano' => 'Dra. María González',
                'email' => 'cie@universidad.edu.ve',
                'telefono' => '0212-5551002',
                'descripcion' => 'Dedicada a la investigación y formación en ciencias básicas y aplicadas.',
                'activo' => true,
            ],
            [
                'codigo' => 'HYE',
                'nombre' => 'Facultad de Humanidades y Educación',
                'decano' => 'Dr. Juan Pérez',
                'email' => 'hye@universidad.edu.ve',
                'telefono' => '0212-5551003',
                'descripcion' => 'Formación integral en humanidades, educación y ciencias sociales.',
                'activo' => true,
            ],
            [
                'codigo' => 'CES',
                'nombre' => 'Facultad de Ciencias Económicas y Sociales',
                'decano' => 'Dr. Roberto Martínez',
                'email' => 'ces@universidad.edu.ve',
                'telefono' => '0212-5551004',
                'descripcion' => 'Formación de profesionales en economía, administración y contaduría.',
                'activo' => true,
            ],
            [
                'codigo' => 'CJP',
                'nombre' => 'Facultad de Ciencias Jurídicas y Políticas',
                'decano' => 'Dra. Ana López',
                'email' => 'cjp@universidad.edu.ve',
                'telefono' => '0212-5551005',
                'descripcion' => 'Formación de profesionales del derecho con excelencia académica.',
                'activo' => true,
            ],
            [
                'codigo' => 'MED',
                'nombre' => 'Facultad de Medicina',
                'decano' => 'Dr. Luis Fernández',
                'email' => 'med@universidad.edu.ve',
                'telefono' => '0212-5551006',
                'descripcion' => 'Formación médica de excelencia con énfasis en la práctica humanista.',
                'activo' => true,
            ],
            [
                'codigo' => 'ODO',
                'nombre' => 'Facultad de Odontología',
                'decano' => 'Dra. Carmen Silva',
                'email' => 'odo@universidad.edu.ve',
                'telefono' => '0212-5551007',
                'descripcion' => 'Formación de odontólogos con alta calidad técnica y humana.',
                'activo' => true,
            ],
            [
                'codigo' => 'FAR',
                'nombre' => 'Facultad de Farmacia',
                'decano' => 'Dr. Pedro Ramírez',
                'email' => 'far@universidad.edu.ve',
                'telefono' => '0212-5551008',
                'descripcion' => 'Formación de profesionales farmacéuticos especializados.',
                'activo' => true,
            ],
            [
                'codigo' => 'ARQ',
                'nombre' => 'Facultad de Arquitectura y Urbanismo',
                'decano' => 'Arq. Laura Torres',
                'email' => 'arq@universidad.edu.ve',
                'telefono' => '0212-5551009',
                'descripcion' => 'Formación de arquitectos y urbanistas con visión innovadora.',
                'activo' => true,
            ],
            [
                'codigo' => 'VET',
                'nombre' => 'Facultad de Ciencias Veterinarias',
                'decano' => 'Dr. Miguel Sánchez',
                'email' => 'vet@universidad.edu.ve',
                'telefono' => '0212-5551010',
                'descripcion' => 'Formación de médicos veterinarios con enfoque integral.',
                'activo' => true,
            ],
        ];

        foreach ($facultades as $facultad) {
            Facultad::create($facultad);
            echo "✓ Facultad creada: {$facultad['nombre']}\n";
        }

        echo "\n✅ Se crearon " . count($facultades) . " facultades.\n\n";
    }
}
