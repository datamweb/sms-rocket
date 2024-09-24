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
use CodeIgniter\Test\CIUnitTestCase;
use Datamweb\SMSRocket\Drivers\IdehpardazanDriver;
use Datamweb\SMSRocket\Models\SMSLogModel;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * @internal
 */
final class IdehpardazanDriverTest extends CIUnitTestCase
{
    private IdehpardazanDriver $driver;
    private MockObject $client;
    private MockObject $model;

    protected function setUp(): void
    {
        parent::setUp();

        // Mocking the CURLRequest and SMSLogModel
        $this->client = $this->createMock(CURLRequest::class);
        $this->model  = $this->createMock(SMSLogModel::class);

        // Initialize IdehpardazanDriver with mocked dependencies
        $this->driver = new IdehpardazanDriver(['api_key' => 'test-api-key'], $this->model, $this->client);
    }

    public function testSend(): void
    {
        $recipient = '09123456789';
        $message   = 'Test message';
        $sender    = 'TestSender';

        // Mocking the response from CURLRequest
        $this->client->method('request')->willReturn($this->createMockResponse(200, json_encode([
            'status' => 1,
            'data'   => ['messageIds' => ['12345']],
        ])));

        $messageId = $this->driver->send($recipient, $message, $sender);
        $this->assertSame('12345', $messageId);
    }

    public function testGetDeliveryStatus(): void
    {
        $messageId = '12345';

        // Mocking the response from CURLRequest
        $this->client->method('request')->willReturn($this->createMockResponse(200, json_encode([
            'status' => 1,
            'data'   => ['deliveryState' => 1], // Assuming deliveryState 1 is delivered
        ])));

        $status = $this->driver->getDeliveryStatus($messageId);
        $this->assertSame('Received', $status); // Assuming 'Delivered' is the title for status code '1'
    }

    public function testSendPatterned(): void
    {
        $recipient     = '09123456789';
        $patternCode   = 'pattern123';
        $patternValues = ['value1', 'value2'];

        // Mocking the response from CURLRequest
        $this->client->method('request')->willReturn($this->createMockResponse(200, json_encode([
            'status' => 1,
            'data'   => ['messageId' => '12345'],
        ])));

        $messageId = $this->driver->sendPatterned($recipient, $patternCode, $patternValues);
        $this->assertSame('12345', $messageId);
    }

    public function testGetCreditBalance(): void
    {
        // Mocking the response from CURLRequest
        $this->client->method('request')->willReturn($this->createMockResponse(200, json_encode([
            'status' => 1,
            'data'   => 100.50,
        ])));

        $creditBalance = $this->driver->getCreditBalance();
        $this->assertEqualsWithDelta(100.50, $creditBalance, PHP_FLOAT_EPSILON);
    }

    public function testSendFailure(): void
    {
        $recipient = '09123456789';
        $message   = 'Test message';
        $sender    = 'TestSender';

        // Mocking a failed response from CURLRequest
        $this->client->method('request')->willReturn($this->createMockResponse(400, json_encode([
            'status' => 0,
        ])));

        $this->expectException(RuntimeException::class);
        $this->driver->send($recipient, $message, $sender);
    }

    public function testGetDeliveryStatusFailure(): void
    {
        $messageId = '12345';

        // Mocking a failed response from CURLRequest
        $this->client->method('request')->willReturn($this->createMockResponse(400, json_encode([
            'status' => 0,
        ])));

        $this->expectException(RuntimeException::class);
        $this->driver->getDeliveryStatus($messageId);
    }

    public function testSendPatternedFailure(): void
    {
        $recipient     = '09123456789';
        $patternCode   = 'pattern123';
        $patternValues = ['value1', 'value2'];

        // Mocking a failed response from CURLRequest
        $this->client->method('request')->willReturn($this->createMockResponse(400, json_encode([
            'status' => 0,
        ])));

        $this->expectException(RuntimeException::class);
        $this->driver->sendPatterned($recipient, $patternCode, $patternValues);
    }

    public function testGetCreditBalanceFailure(): void
    {
        // Mocking a failed response from CURLRequest
        $this->client->method('request')->willReturn($this->createMockResponse(400, json_encode([
            'status' => 0,
        ])));

        $this->expectException(RuntimeException::class);
        $this->driver->getCreditBalance();
    }

    public function testSendThrowsRuntimeExceptionOnUnsuccessfulStatus(): void
    {
        // Arrange: Mock the CURLRequest to return a failed response
        $this->client->method('request')->willReturn($this->createMockResponse(200, json_encode([
            'status' => 0,
        ])));

        // Assert: Expect a RuntimeException to be thrown
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('API returned an unsuccessful status or missing data.');

        // Act: Call the send method, which should throw the exception
        $this->driver->send('09123456783', 'Test message', 'Sender');
    }

    protected function createMockResponse(int $statusCode, string $body): object
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getBody')->willReturn($body);

        return $response;
    }
}
