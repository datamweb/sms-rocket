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

namespace Tests\Drivers;

use BadMethodCallException;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use Datamweb\SMSRocket\Drivers\TwilioDriver;
use Datamweb\SMSRocket\Enums\Twilio\DeliveryStatus;
use Datamweb\SMSRocket\Exceptions\SMSException;
use Datamweb\SMSRocket\Models\SMSLogModel;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test for TwilioDriver.
 *
 * @internal
 */
final class TwilioDriverTest extends CIUnitTestCase
{
    private TwilioDriver $driver;
    private MockObject $model;
    private MockObject $client;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock dependencies
        $this->model  = $this->createMock(SMSLogModel::class);
        $this->client = $this->createMock(CURLRequest::class);

        // Configuration for the driver
        $config = [
            'AccountSID' => 'testSID',
            'AuthToken'  => 'testToken',
        ];

        // Instantiate the driver with mocks
        $this->driver = new TwilioDriver($config, $this->model, $this->client);
    }

    public function testSendSuccess(): void
    {
        // Mock successful response from Twilio
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn(201);
        $response->method('getBody')->willReturn(json_encode(['sid' => '12345', 'status' => 'sent']));

        // Expect client to send a request
        $this->client->expects($this->once())
            ->method('request')
            ->with('POST')
            ->willReturn($response);

        // Expect model to log the SMS
        $this->model->expects($this->once())
            ->method('logSMS')
            ->with(
                TwilioDriver::class,
                '12345',
                '+1234567890',
                DeliveryStatus::fromCode('sent')->toNumericCode(),
                'Test message',
                null,
                'TestSender'
            );

        $messageId = $this->driver->send('+1234567890', 'Test message', 'TestSender');

        $this->assertSame('12345', $messageId);
    }

    public function testSendFailure(): void
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn(400);

        $this->client->method('request')->willReturn($response);

        $this->expectException(SMSException::class);
        $this->driver->send('+1234567890', 'Test message', 'TestSender');
    }

    public function testGetDeliveryStatusSuccess(): void
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn(json_encode(['status' => 'delivered']));

        $this->client->method('request')->willReturn($response);

        $this->model->expects($this->once())
            ->method('updateStatus')
            ->with('12345', DeliveryStatus::fromCode('delivered')->toNumericCode());

        $status = $this->driver->getDeliveryStatus('12345');

        $this->assertSame('Delivered', $status);
    }

    public function testGetCreditBalanceSuccess(): void
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn(json_encode(['balance' => '100.50']));

        $this->client->method('request')->willReturn($response);

        $balance = $this->driver->getCreditBalance();

        $this->assertEqualsWithDelta(100.5, $balance, PHP_FLOAT_EPSILON);
    }

    public function testSendPatternedThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->driver->sendPatterned('+1234567890', 'code', ['name' => 'John']);
    }
}
