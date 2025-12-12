<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCarrerasYEstudiantes extends Migration
{
    public function up()
    {
        /*
         * Tabla: carreras
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);

        $this->forge->addKey('id', true); // primary key
        $this->forge->createTable('carreras', true);

        /*
         * Tabla: estudiantes
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'edad' => [
                'type'       => 'INT',
                'constraint' => 3,
                'unsigned'   => true,
            ],
            'idCarrera' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        // Llave foránea: idCarrera -> carreras.id
        $this->forge->addForeignKey('idCarrera', 'carreras', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('estudiantes', true);
    }

    public function down()
    {
        // Borrar la que tiene foreign key
        $this->forge->dropTable('estudiantes', true);
        $this->forge->dropTable('carreras', true);
    }
}
