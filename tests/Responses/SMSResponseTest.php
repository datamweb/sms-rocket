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

namespace Tests\Responses;

use Datamweb\SMSRocket\Responses\SMSResponse;
use Tests\Support\TestCase;

/**
 * Class SMSResponseTest
 *
 * This class contains unit tests for the SMSResponse class.
 * It tests the constructor, getter methods, and string conversion of the SMSResponse.
 *
 * @internal
 */
final class SMSResponseTest extends TestCase
{
    /**
     * Tests the constructor and getter methods of SMSResponse.
     *
     * This test verifies that the constructor correctly assigns values for
     * the successful flag, message, recipient, and message ID.
     */
    public function testConstructorAndGetters(): void
    {
        $successful = true;
        $message    = 'Message sent successfully';
        $recipient  = '09123456789';
        $messageId  = '12345';

        $response = new SMSResponse($successful, $message, $recipient, $messageId);

        $this->assertTrue($response->isOK());
        $this->assertSame($message, $response->getMessage());
        $this->assertSame($recipient, $response->getRecipient());
        $this->assertSame($messageId, $response->getMessageId());
    }

    /**
     * Tests that the message ID can be null.
     *
     * This test ensures that when no message ID is provided to the constructor,
     * the message ID is set to null.
     */
    public function testMessageIdCanBeNull(): void
    {
        $successful = false;
        $message    = 'Failed to send message';
        $recipient  = '09123456789';

        $response = new SMSResponse($successful, $message, $recipient);

        $this->assertNull($response->getMessageId());
    }

    /**
     * Tests the string representation of the SMSResponse.
     *
     * This test checks the __toString method to ensure it returns the
     * recipient and message in the correct format.
     */
    public function testToString(): void
    {
        $successful = true;
        $message    = 'Message sent successfully';
        $recipient  = '09123456789';

        $response = new SMSResponse($successful, $message, $recipient);

        $expectedString = 'Recipient: 09123456789, Message: Message sent successfully';
        $this->assertSame($expectedString, (string) $response);
    }
}
