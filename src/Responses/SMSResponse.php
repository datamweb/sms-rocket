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

namespace Datamweb\SMSRocket\Responses;

use Stringable;

/**
 * Class SMSResponse
 *
 * Represents the response from an SMS operation, including details about the recipient.
 *
 * This class encapsulates the success status, message content, and recipient information
 * of an SMS operation, providing easy access to the outcome of the operation and any
 * relevant information for further processing or logging.
 */
class SMSResponse implements Stringable
{
    /**
     * Constructor to initialize the response.
     *
     * @param bool   $successful Indicates if the SMS was successfully sent to the provider.
     * @param string $message    The message or response from the SMS operation.
     * @param string $recipient  The recipient of the SMS.
     */
    public function __construct(
        protected bool $successful,
        protected string $message,
        protected string $recipient,
        /**
         * @var string|null The message ID of the SMS operation, or null if not available.
         */
        protected ?string $messageId = null
    ) {
    }

    /**
     * Determine if the SMS was successfully sent to the provider.
     *
     * @return bool True if the SMS was successfully sent to the provider, false otherwise.
     */
    public function isOK(): bool
    {
        return $this->successful;
    }

    /**
     * Get the response message.
     *
     * @return string The message or response from the SMS operation.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the message ID of the SMS operation.
     *
     * @return string|null The message ID of the SMS operation, or null if not available.
     */
    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    /**
     * Get the recipient of the SMS.
     *
     * @return string The recipient of the SMS.
     */
    public function getRecipient(): string
    {
        return $this->recipient;
    }

    /**
     * Convert the response to a string representation.
     *
     * This method returns a string that includes both the recipient and the response message.
     *
     * @return string The message of the response with the recipient.
     */
    public function __toString(): string
    {
        return "Recipient: {$this->recipient}, Message: {$this->message}";
    }
}
