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

namespace Datamweb\SMSRocket\Drivers;

use InvalidArgumentException;
use RuntimeException;

/**
 * Interface SMSDriverInterface
 *
 * Defines the contract for an SMS driver.
 * Any class implementing this interface must provide
 * the functionality to send SMS messages and retrieve
 * the status of sent messages.
 */
interface SMSDriverInterface
{
    /**
     * Sends an SMS message to a specified recipient.
     *
     * @param string $recipient The phone number of the recipient.
     * @param string $message   The message content to be sent.
     *
     * @return string The unique identifier for the sent message.
     *                This identifier can be used to track the message status.
     *
     * @throws InvalidArgumentException If the recipient or message is invalid.
     */
    public function send(string $recipient, string $message, string $sender): string;

    /**
     * Retrieves the status of a sent SMS message.
     *
     * @param string $messageId The unique identifier of the message.
     *
     * @return string The current status of the message.
     *                Possible values may include "sent", "delivered", "failed", etc.
     *
     * @throws InvalidArgumentException If the message ID is invalid.
     * @throws RuntimeException         If there is an issue retrieving the status.
     */
    public function getDeliveryStatus(string $messageId): string;

    /**
     * Retrieves the current credit balance from the SMS provider.
     *
     * The balance may be returned as an integer for whole numbers or as a float
     * for fractional values.
     *
     * @return float|int The current credit balance.
     */
    public function getCreditBalance(): float|int;

    public function sendPatterned(string $recipient, string $patternCode, array $patternValues): string;
}
