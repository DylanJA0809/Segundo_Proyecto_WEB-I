<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CarrerasYEstudiantesSeeder extends Seeder
{
    public function run()
    {
        // Carreras ejemplo
        $carreras = [
            ['nombre' => 'Ingeniería en Software'],
            ['nombre' => 'Ingeniería en Redes'],
            ['nombre' => 'Administración de Empresas'],
        ];

        $this->db->table('carreras')->insertBatch($carreras);

        // Estudiantes ejemplo
        $estudiantes = [
            [
                'nombre'    => 'Juan Pérez',
                'edad'      => 20,
                'idCarrera' => 1, // Ingeniería en Software
            ],
            [
                'nombre'    => 'María López',
                'edad'      => 22,
                'idCarrera' => 2, // Ingeniería en Redes
            ],
        ];

        $this->db->table('estudiantes')->insertBatch($estudiantes);
    }
}
