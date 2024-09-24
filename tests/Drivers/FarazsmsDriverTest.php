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

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Response;
use Datamweb\SMSRocket\Drivers\FarazsmsDriver;
use Datamweb\SMSRocket\Models\SMSLogModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class FarazsmsDriverTest extends TestCase
{
    private FarazsmsDriver $driver;
    private MockObject $client;
    private MockObject $model;

    protected function setUp(): void
    {
        $this->client = $this->createMock(CURLRequest::class);
        $this->model  = $this->createMock(SMSLogModel::class);
        $config       = ['api_key' => 'test_api_key'];

        $this->driver = new FarazsmsDriver($config, $this->model, $this->client);
    }

    public function testSendSuccess(): void
    {
        $this->client->method('request')->willReturn($this->createSuccessfulResponse());

        $messageId = $this->driver->send('09123456789', 'Test message', 'TestSender');

        $this->assertSame('12345', $messageId);
    }

    public function testSendFailure(): void
    {
        $this->client->method('request')->willReturn($this->createFailureResponse());

        $this->expectException(RuntimeException::class);
        $this->driver->send('09123456789', 'Test message', 'TestSender');
    }

    public function testGetDeliveryStatusSuccess(): void
    {
        $this->client->method('request')->willReturn($this->createSuccessfulDeliveryStatusResponse());

        $status = $this->driver->getDeliveryStatus('12345');

        $this->assertSame('Delivered', $status);
    }

    public function testGetDeliveryStatusFailure(): void
    {
        $this->client->method('request')->willReturn($this->createFailureResponse());

        $this->expectException(RuntimeException::class);
        $this->driver->getDeliveryStatus('12345');
    }

    public function testSendPatternedSuccess(): void
    {
        $this->client->method('request')->willReturn($this->createSuccessfulResponse());

        $messageId = $this->driver->sendPatterned('09123456789', 'pattern_code', ['value1', 'value2']);

        $this->assertSame('12345', $messageId);
    }

    public function testSendPatternedFailure(): void
    {
        $this->client->method('request')->willReturn($this->createFailureResponse());

        $this->expectException(RuntimeException::class);
        $this->driver->sendPatterned('09123456789', 'pattern_code', ['value1', 'value2']);
    }

    public function testGetCreditBalanceSuccess(): void
    {
        $this->client->method('request')->willReturn($this->createSuccessfulCreditBalanceResponse());

        $balance = $this->driver->getCreditBalance();

        $this->assertEqualsWithDelta(100.0, $balance, PHP_FLOAT_EPSILON);
    }

    public function testGetCreditBalanceFailure(): void
    {
        $this->client->method('request')->willReturn($this->createFailureResponse());

        $this->expectException(RuntimeException::class);
        $this->driver->getCreditBalance();
    }

    protected function createSuccessfulResponse()
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn(json_encode([
            'status' => 'OK',
            'data'   => ['message_id' => '12345'],
        ]));

        return $response;
    }

    protected function createFailureResponse()
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getBody')->willReturn(json_encode(['status' => 'ERROR']));

        return $response;
    }

    protected function createSuccessfulDeliveryStatusResponse()
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn(json_encode([
            'status' => 'OK',
            'data'   => ['deliveries' => [['status' => 2]]],
        ]));

        return $response;
    }

    protected function createSuccessfulCreditBalanceResponse()
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn(json_encode([
            'status' => 'OK',
            'data'   => ['credit' => 100.0],
        ]));

        return $response;
    }
}
