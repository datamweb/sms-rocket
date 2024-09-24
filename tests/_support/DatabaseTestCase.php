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

namespace Tests\Support;

use CodeIgniter\Test\DatabaseTestTrait;
use Datamweb\SMSRocket\Config\SMSRocketConfig;

/**
 * @internal
 */
abstract class DatabaseTestCase extends TestCase
{
    use DatabaseTestTrait;

    protected $namespace = '\Datamweb\SMSRocket';

    /**
     * SMSRocket Table name
     */
    protected string $table;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var SMSRocketConfig $smsRocketConfig */
        $smsRocketConfig = config('SMSRocketConfig');
        $this->table     = $smsRocketConfig->table;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
