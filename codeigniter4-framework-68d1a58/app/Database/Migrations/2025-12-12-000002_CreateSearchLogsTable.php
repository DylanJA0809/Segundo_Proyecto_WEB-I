<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateSearchLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true, // por si alguien busca sin estar logueado
            ],
            'departure' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'arrival' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'results_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('search_logs');
    }

    public function down()
    {
        $this->forge->dropTable('search_logs');
    }
}
