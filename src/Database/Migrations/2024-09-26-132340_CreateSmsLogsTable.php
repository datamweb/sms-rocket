<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter SMSRocket.
 *
 * (c) Pooya Parsa Dadashi <admin@codeigniter4.ir>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Datamweb\SMSRocket\Database\Migrations;

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;
use Datamweb\SMSRocket\Config\SMSRocketConfig;

class CreateSmsLogsTable extends Migration
{
    /**
     * SMSRocket Table name
     */
    private readonly string $tableName;

    private readonly array $attributes;

    public function __construct(?Forge $forge = null)
    {
        /** @var SMSRocketConfig $smsRocketConfig */
        $smsRocketConfig = config('SMSRocketConfig');

        if ($smsRocketConfig->DBGroup !== null) {
            $this->DBGroup = $smsRocketConfig->DBGroup;
        }

        parent::__construct($forge);

        $this->tableName  = $smsRocketConfig->table;
        $this->attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];
    }

    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'provider_name' => ['type' => 'TEXT', 'VARCHAR' => 20, 'null' => false],
            'from'          => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true],
            'to'            => ['type' => 'VARCHAR', 'constraint' => '20'],
            'message'       => ['type' => 'TEXT', 'null' => true],
            'message_id'    => ['type' => 'varchar', 'constraint' => 50, 'null' => true],
            'template_id'   => ['type' => 'varchar', 'constraint' => 25, 'null' => true],
            'status'        => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'default' => null],
            'response'      => ['type' => 'TEXT', 'null' => true],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable($this->tableName, false, $this->attributes);
    }

    public function down(): void
    {
        $this->forge->dropTable($this->tableName);
    }
}
