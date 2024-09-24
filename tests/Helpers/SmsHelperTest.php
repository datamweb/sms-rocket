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

namespace Tests\Helpers;

use Config\Services;
use Datamweb\SMSRocket\Services\SMSRocketService;
use Tests\Support\TestCase;

/**
 * Class SmsHelperTest
 *
 * Unit tests for the sms() function.
 *
 * @internal
 */
final class SmsHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('sms');
    }

    /**
     * Tests that the sms() function returns an instance of SMSRocketService.
     */
    public function testSmsFunctionReturnsSmsRocketService(): void
    {
        // Create a mock of SMSRocketService
        $mockService = $this->createMock(SMSRocketService::class);

        // Set up the service container to return the mock service when 'smsRocket' is requested
        Services::injectMock('smsRocket', $mockService);

        // Call the sms() function
        $result = sms();

        // Assert that the result is an instance of SMSRocketService
        $this->assertInstanceOf(SMSRocketService::class, $result);

        // Clean up the mock service
        Services::reset();
    }

    /**
     * Tests that the sms() function returns the correct service instance when not mocked.
     */
    public function testSmsFunctionReturnsCorrectServiceInstance(): void
    {
        // Call the sms() function without mocking
        $result = sms();

        // Assert that the result is an instance of SMSRocketService
        $this->assertInstanceOf(SMSRocketService::class, $result);
    }
}
