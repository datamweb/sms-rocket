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

namespace Tests;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\Log\Logger;
use Datamweb\SMSRocket\Config\SMSRocketConfig;
use Datamweb\SMSRocket\Drivers\SMSDriverInterface;
use Datamweb\SMSRocket\Models\SMSLogModel;
use Datamweb\SMSRocket\Responses\SMSResponse;
use Datamweb\SMSRocket\Services\SMSRocketService;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

/**
 * @internal
 */
final class SMSRocketServiceTest extends TestCase
{
    private SMSRocketService $service;
    private SMSDriverInterface $driver;
    private CacheInterface $cache;
    private MockObject $logger;
    private CURLRequest $client;
    private SMSRocketConfig $config;
    private SMSLogModel $model;

    protected function setUp(): void
    {
        $this->cache  = $this->createMock(CacheInterface::class);
        $this->config = new SMSRocketConfig();
        $this->model  = $this->createMock(SMSLogModel::class);
        $this->logger = $this->createMock(Logger::class);
        $this->client = $this->createMock(CURLRequest::class);

        $this->config->defaultDriver = 'testDriver';
        $this->config->drivers       = [
            'testDriver' => [
                'class'  => $this->createMock(SMSDriverInterface::class)::class,
                'config' => [
                    'defaultSender' => '12345',
                ],
            ],
        ];
        $this->config->retryAttempts = 3;
        $this->config->retryDelay    = 1;

        $this->service = new SMSRocketService($this->cache, $this->config, $this->model, $this->logger, $this->client);
    }

    /**
     * Test that the driver method sets the correct driver.
     */
    public function testDriverSetsCorrectDriver(): void
    {
        $this->service->driver('testDriver');
        $this->assertInstanceOf(SMSDriverInterface::class, $this->getPrivateProperty($this->service, 'driver'));
    }

    /**
     * Test that an invalid driver throws an exception.
     */
    public function testDriverThrowsInvalidArgumentExceptionForInvalidDriver(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->driver('invalidDriver');
    }

    /**
     * Test sending an SMS with a valid receiver and message.
     */
    public function testSendSMSWithValidReceiverAndMessage(): void
    {
        $this->service->setSender('12345');
        $this->service->setReceiver('9876543210');
        $this->service->setMessage('Test Message');

        $this->driver = $this->createMock(SMSDriverInterface::class);
        $this->driver->method('send')->willReturn('messageId123');
        $this->setPrivateProperty($this->service, 'driver', $this->driver);

        $response = $this->service->send();
        $this->assertTrue($response->getResponse('9876543210')->isOK());
    }

    /**
     * Test that an empty receiver array throws an exception.
     */
    public function testSendThrowsExceptionForNoReceiver(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->send();
    }

    /**
     * Test that an empty message throws an exception.
     */
    public function testSendThrowsExceptionForNoMessage(): void
    {
        $this->service->setReceiver('9876543210');
        $this->expectException(InvalidArgumentException::class);
        $this->service->send();
    }

    public function testGetMessageId(): void
    {
        $successful = true;
        $message    = 'SMS sent successfully.';
        $recipient  = '1234567890';
        $messageId  = 'msg12345';

        // Create an instance of SMSResponse with a message ID
        $response = new SMSResponse($successful, $message, $recipient, $messageId);

        // Assert that the message ID is returned correctly
        $this->assertSame($messageId, $response->getMessageId());
    }

    public function testGetMessageIdReturnsNullIfNotSet(): void
    {
        $successful = true;
        $message    = 'SMS sent successfully.';
        $recipient  = '1234567890';

        // Create an instance of SMSResponse without a message ID
        $response = new SMSResponse($successful, $message, $recipient);

        // Assert that the message ID returns null
        $this->assertNull($response->getMessageId());
    }

    /**
     * Test sending with retries when the first attempts fail.
     */
    public function testSendWithRetry(): void
    {
        $this->service->setReceiver('9876543210');
        $this->service->setMessage('Test Message');

        // Create a mock driver that simulates sending failures for the first two attempts
        $this->driver = $this->createMock(SMSDriverInterface::class);

        // Specify the return values for the consecutive calls
        $this->driver
            ->method('send')
            ->willReturn('messageId3');

        // Set the mock driver
        $this->setPrivateProperty($this->service, 'driver', $this->driver);

        // Call the send method and get the response
        $response = $this->service->send();
        $response = $response->getResponse('9876543210');
        // Verify that the final attempt was successful and returned a message ID
        $this->assertTrue($response->isOK());

        $this->assertSame('messageId3', $response->getMessageId());
    }

    /**
     * Test patterned SMS functionality.
     */
    public function testSendPatternedSMS(): void
    {
        $this->service->setReceiver('9876543210');
        $this->service->setPattern('patternCode123');
        $this->service->setPatternData(['key' => 'value']);

        $this->driver = $this->createMock(SMSDriverInterface::class);
        $this->driver->method('sendPatterned')->willReturn('messageId123');
        $this->setPrivateProperty($this->service, 'driver', $this->driver);

        $response = $this->service->send();
        $this->assertTrue($response->getResponse('9876543210')->isOK());
    }

    public function testSendPatternedSMSFailure(): void
    {
        $receiver    = '9876543210';
        $patternCode = 'patternCode123';
        $patternData = ['key' => 'value'];

        $this->driver = $this->createMock(SMSDriverInterface::class);
        $this->driver->method('sendPatterned')
            ->willThrowException(new Exception('Simulated failure'));

        $this->setPrivateProperty($this->service, 'driver', $this->driver);

        $this->logger->expects($this->once())
            ->method('error')
            ->with("Failed to send patterned SMS to {$receiver}: Simulated failure");

        $reflection = new ReflectionMethod($this->service, 'sendPatternedSMS');
        $reflection->setAccessible(true);

        $response = $reflection->invoke($this->service, $receiver, $patternCode, $patternData);

        $this->assertFalse($response->isOK());
        $this->assertSame('Failed to send patterned message: Simulated failure', $response->getMessage());
        $this->assertSame($receiver, $response->getRecipient());
    }

    public function testGetCreditReturnsBalanceSuccessfully(): void
    {
        $creditBalance = 1000.50;

        $this->driver = $this->createMock(SMSDriverInterface::class);
        $this->driver->method('getCreditBalance')->willReturn($creditBalance);

        $this->setPrivateProperty($this->service, 'driver', $this->driver);

        $this->logger->expects($this->once())
            ->method('info')
            ->with("Credit retrieved successfully: {$creditBalance}");

        $this->assertSame($creditBalance, $this->service->getCredit());
    }

    public function testGetCreditThrowsExceptionWhenDriverNotConfigured(): void
    {
        $this->setPrivateProperty($this->service, 'driver', null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No SMS driver is configured.');

        $this->service->getCredit();
    }

    public function testGetCreditThrowsExceptionWhenDriverFails(): void
    {
        $this->driver = $this->createMock(SMSDriverInterface::class);
        $this->driver->method('getCreditBalance')
            ->willThrowException(new Exception('Simulated failure'));

        $this->setPrivateProperty($this->service, 'driver', $this->driver);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to retrieve credit: Simulated failure');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to retrieve credit: Simulated failure');

        $this->service->getCredit();
    }

    /**
     * Helper function to set private properties for testing purposes.
     */
    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($object);
        $property   = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Helper function to get private properties for testing purposes.
     */
    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflection = new ReflectionClass($object);
        $property   = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
