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

use Datamweb\SMSRocket\Responses\SMSMultiResponse;
use Datamweb\SMSRocket\Responses\SMSResponse;
use Tests\Support\TestCase;

/**
 * Class SMSMultiResponseTest
 *
 * Unit tests for the SMSMultiResponse class.
 *
 * @internal
 */
final class SMSMultiResponseTest extends TestCase
{
    /**
     * Tests the addition of a single SMS response.
     */
    public function testAddResponse(): void
    {
        $multiResponse = new SMSMultiResponse();
        $response      = new SMSResponse(true, 'Message sent successfully.', '1234567890', 'messageId123');

        $multiResponse->addResponse('1234567890', $response);

        $this->assertSame($response, $multiResponse->getResponse('1234567890'));
    }

    /**
     * Tests the retrieval of a response for a non-existent recipient.
     */
    public function testGetResponseNotFound(): void
    {
        $multiResponse = new SMSMultiResponse();

        $this->assertNull($multiResponse->getResponse('non_existent_recipient'));
    }

    /**
     * Tests getting all responses.
     */
    public function testGetAllResponses(): void
    {
        $multiResponse = new SMSMultiResponse();
        $response1     = new SMSResponse(true, 'First message sent.', '09112004040');
        $response2     = new SMSResponse(false, 'Second message failed.', '0987654321');

        $multiResponse->addResponse('09112004040', $response1);
        $multiResponse->addResponse('0987654321', $response2);

        $responses = $multiResponse->getAllResponses();

        $this->assertCount(2, $responses);
        $this->assertSame($response1, $responses['09112004040']);
        $this->assertSame($response2, $responses['0987654321']);
    }

    /**
     * Tests the allSuccessful method when all responses are successful.
     */
    public function testAllSuccessfulTrue(): void
    {
        $multiResponse = new SMSMultiResponse();
        $response1     = new SMSResponse(true, 'First message sent.', '1234567890');
        $response2     = new SMSResponse(true, 'Second message sent.', '0987654321');

        $multiResponse->addResponse('1234567890', $response1);
        $multiResponse->addResponse('0987654321', $response2);

        $this->assertTrue($multiResponse->allOK());
    }

    /**
     * Tests the allSuccessful method when not all responses are successful.
     */
    public function testAllSuccessfulFalse(): void
    {
        $multiResponse = new SMSMultiResponse();
        $response1     = new SMSResponse(true, 'First message sent.', '1234567890');
        $response2     = new SMSResponse(false, 'Second message failed.', '0987654321');

        $multiResponse->addResponse('1234567890', $response1);
        $multiResponse->addResponse('0987654321', $response2);

        $this->assertFalse($multiResponse->allOK());
    }

    /**
     * Tests the string representation of the SMSMultiResponse.
     */
    public function testToString(): void
    {
        $multiResponse = new SMSMultiResponse();
        $response1     = new SMSResponse(true, 'Message 1 sent.', '1234567890');
        $response2     = new SMSResponse(false, 'Message 2 failed.', '0987654321');

        $multiResponse->addResponse('1234567890', $response1);
        $multiResponse->addResponse('0987654321', $response2);

        $expected = "Recipient: 1234567890, Response: Recipient: 1234567890, Message: Message 1 sent.\n" .
            'Recipient: 0987654321, Response: Recipient: 0987654321, Message: Message 2 failed.';

        $this->assertSame($expected, (string) $multiResponse);
    }
}
