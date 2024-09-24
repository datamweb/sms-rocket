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

namespace Datamweb\SMSRocket\Enums\Twilio;

/**
 * Enum representing Twilio SMS delivery status codes and their numeric equivalents.
 */
enum DeliveryStatus: string
{
    /**
     * Map status string to a numeric code for storage in database.
     */
    public function toNumericCode(): int
    {
        return match ($this) {
            self::Queued             => 1,
            self::Sending            => 2,
            self::Sent               => 3,
            self::Failed             => 4,
            self::Delivered          => 5,
            self::Undelivered        => 6,
            self::Receiving          => 7,
            self::Received           => 8,
            self::Accepted           => 9,
            self::Scheduled          => 10,
            self::Read               => 11,
            self::PartiallyDelivered => 12,
            self::Canceled           => 13,
            self::Unknown            => 14,
        };
    }

    /**
     * Retrieve the DeliveryStatus enum based on the numeric code from the database.
     *
     * @param int $code The numeric code to look up.
     *
     * @return DeliveryStatus The matching status enum.
     */
    public static function fromNumericCode(int $code): DeliveryStatus
    {
        return match ($code) {
            1       => self::Queued,
            2       => self::Sending,
            3       => self::Sent,
            4       => self::Failed,
            5       => self::Delivered,
            6       => self::Undelivered,
            7       => self::Receiving,
            8       => self::Received,
            9       => self::Accepted,
            10      => self::Scheduled,
            11      => self::Read,
            12      => self::PartiallyDelivered,
            13      => self::Canceled,
            14      => self::Unknown,
            default => self::Unknown,
        };
    }

    /**
     * Get the delivery status title directly from the code.
     */
    public static function getTitleFromCode(int|string $code): string
    {
        return self::fromNumericCode((int) $code)->title();
    }

    /**
     * Get the title of the delivery status using the lang() function.
     *
     * @return string The title of the status.
     */
    public function title(): string
    {
        return lang('Twilio.' . $this->name);
    }

    /**
     * Retrieve the DeliveryStatus enum based on the provided string code from API.
     *
     * @param string $code The status string received from the API.
     *
     * @return DeliveryStatus The matching status enum or Unknown if not found.
     */
    public static function fromCode(string $code): DeliveryStatus
    {
        return match ($code) {
            'queued'              => self::Queued,
            'sending'             => self::Sending,
            'sent'                => self::Sent,
            'failed'              => self::Failed,
            'delivered'           => self::Delivered,
            'undelivered'         => self::Undelivered,
            'receiving'           => self::Receiving,
            'received'            => self::Received,
            'accepted'            => self::Accepted,
            'scheduled'           => self::Scheduled,
            'read'                => self::Read,
            'partially_delivered' => self::PartiallyDelivered,
            'canceled'            => self::Canceled,
            default               => self::Unknown, // Return Unknown as default if code is invalid
        };
    }
    case Queued             = 'queued';
    case Sending            = 'sending';
    case Sent               = 'sent';
    case Failed             = 'failed';
    case Delivered          = 'delivered';
    case Undelivered        = 'undelivered';
    case Receiving          = 'receiving';
    case Received           = 'received';
    case Accepted           = 'accepted';
    case Scheduled          = 'scheduled';
    case Read               = 'read';
    case PartiallyDelivered = 'partially_delivered';
    case Canceled           = 'canceled';
    case Unknown            = 'unknown';
}
