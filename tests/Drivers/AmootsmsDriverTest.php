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
use CodeIgniter\HTTP\ResponseInterface;
use Datamweb\SMSRocket\Drivers\AmootsmsDriver;
use Datamweb\SMSRocket\Exceptions\SMSException;
use Datamweb\SMSRocket\Models\SMSLogModel;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class AmootsmsDriverTest extends TestCase
{
    private AmootsmsDriver $driver;
    private MockObject $mockModel;
    private MockObject $mockClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockModel  = $this->createMock(SMSLogModel::class);
        $this->mockClient = $this->createMock(CURLRequest::class);

        $config = [
            'token' => 'test-token',
        ];

        $this->driver = new AmootsmsDriver($config, $this->mockModel, $this->mockClient);
    }

    public function testSendSuccess(): void
    {
        $recipient = '09120000000';
        $message   = 'Test message';

        // Mocking the response from the API
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn(json_encode([
            'Status' => 'Success',
            'Data'   => [['MessageID' => '12345']], // Correct structure for 'Data'
        ]));

        // Mocking the HTTP client
        $this->mockClient->method('request')->willReturn($responseMock);

        // Sending the message using the driver
        $result = $this->driver->send($recipient, $message, '');

        // Assert that the returned result is the MessageID, not 'Success'
        $this->assertSame('12345', $result); // Expecting the MessageID to be returned
    }

    public function testSendFailure(): void
    {
        // انتظار داریم که RuntimeException رخ دهد.
        $this->expectException(SMSException::class);
        $this->expectExceptionMessage('Error sending simple SMS: API returned an unsuccessful status or missing data.'); // تطبیق با پیام واقعی

        $recipient = '09120000000';
        $message   = 'Test message';

        // Mocking the response to simulate a failure
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn(json_encode(['Status' => 'Error', 'Message' => 'Error sending simple SMS: API returned an unsuccessful status or missing data.']));

        // Mocking the HTTP client to return the failure response
        $this->mockClient->method('request')->willReturn($responseMock);

        // Calling the send method and expecting an exception
        $this->driver->send($recipient, $message, '');
    }

    public function testGetDeliveryStatusSuccess(): void
    {
        $messageId = '12345';

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn(json_encode(['Status' => 'Success', 'Data' => ['DeliveryType' => 1]]));

        $this->mockClient->method('request')->willReturn($responseMock);

        $result = $this->driver->getDeliveryStatus($messageId);

        $this->assertSame('Received by phone', $result);
    }

    public function testGetDeliveryStatusFailure(): void
    {
        $this->expectException(RuntimeException::class);

        $messageId = '12345';

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn(json_encode(['Status' => 'Error']));

        $this->mockClient->method('request')->willReturn($responseMock);

        $this->driver->getDeliveryStatus($messageId);
    }

    public function testSendPatternedSuccess(): void
    {
        $recipient     = '09120000000';
        $patternCode   = 'pattern1';
        $patternValues = ['value1', 'value2'];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn(json_encode(['Status' => 'Success', 'Data' => [['MessageID' => '12345']]]));

        $this->mockClient->method('request')->willReturn($responseMock);

        $result = $this->driver->sendPatterned($recipient, $patternCode, $patternValues);
        $this->assertSame('12345', $result);
    }

    public function testSendPatternedFailure(): void
    {
        $this->expectException(RuntimeException::class);

        $recipient     = '09120000000';
        $patternCode   = 'pattern1';
        $patternValues = ['value1', 'value2'];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn(json_encode(['Status' => 'Error']));

        $this->mockClient->method('request')->willReturn($responseMock);

        $this->driver->sendPatterned($recipient, $patternCode, $patternValues);
    }

    public function testGetCreditBalanceSuccess(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn(json_encode(['Status' => 'Success', 'RemaindCredit' => 100]));

        $this->mockClient->method('request')->willReturn($responseMock);

        $result = $this->driver->getCreditBalance();
        $this->assertSame(100, $result);
    }

    public function testGetCreditBalanceFailure(): void
    {
        $this->expectException(RuntimeException::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getBody')->willReturn(json_encode(['Status' => 'Error']));

        $this->mockClient->method('request')->willReturn($responseMock);

        $this->driver->getCreditBalance();
    }

    public function testGetCreditBalanceException(): void
    {
        $this->expectException(RuntimeException::class);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(500);

        $this->mockClient->method('request')->willReturn($responseMock);

        $this->driver->getCreditBalance();
    }
}
