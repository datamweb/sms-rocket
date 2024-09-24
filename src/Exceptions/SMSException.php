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

namespace Datamweb\SMSRocket\Exceptions;

use RuntimeException;

/**
 * Class SMSException
 *
 * Custom exception class for handling SMS-related errors.
 */
class SMSException extends RuntimeException
{
    /**
     * Throws an exception for an unsuccessful API status.
     */
    public static function forUnsuccessfulAPIStatus(): self
    {
        return new self(lang('SMSException.unsuccessfulAPIStatus'));
    }

    /**
     * Throws an exception for a failed API response.
     */
    public static function forFailedAPIResponse(): self
    {
        return new self(lang('SMSException.failedAPIResponse'));
    }

    /**
     * Throws an exception for an error encountered while sending a simple SMS.
     *
     * @param string $message The error message describing the failure.
     */
    public static function forErrorSendingSimpleSMS(string $message): self
    {
        return new self(lang('SMSException.errorSendingSimpleSMS', [$message]));
    }

    /**
     * Throws an exception for a failed delivery status.
     *
     * @param string $messageId The ID of the message whose delivery status has failed.
     */
    public static function forFailedDeliveryStatus(string $messageId): self
    {
        return new self(lang('SMSException.failedDeliveryStatus', [$messageId]));
    }

    /**
     * Throws an exception for an error encountered while sending a patterned SMS.
     *
     * @param string $message The error message describing the failure.
     */
    public static function forErrorSendingPatternedSMS(string $message): self
    {
        return new self(lang('SMSException.errorSendingPatternedSMS', [$message]));
    }

    /**
     * Throws an exception for an error encountered while retrieving the credit balance.
     *
     * @param string $message The error message describing the failure.
     */
    public static function forErrorRetrievingCreditBalance(string $message): self
    {
        return new self(lang('SMSException.errorRetrievingCreditBalance', [$message]));
    }
}
