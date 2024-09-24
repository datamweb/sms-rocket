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

namespace Datamweb\SMSRocket\Enums\Farazsms;

/**
 * Enum representing SMS delivery status codes and their descriptions.
 */
enum DeliveryStatus: int
{
    /**
     * Get the delivery status title directly from the code.
     */
    public static function getTitleFromCode(int|string $code): string
    {
        return self::fromCode($code)->title();
    }

    /**
     * Get the title of the delivery status using the lang() function.
     *
     * @return string The title of the status.
     */
    public function title(): string
    {
        return lang('Farazsms.' . $this->name);
    }

    /**
     * Retrieve the DeliveryStatus enum based on the provided code.
     *
     * @param int|string $code The code to look up.
     *
     * @return DeliveryStatus The matching status enum or the default Unknown status if not found.
     */
    public static function fromCode(int|string $code): DeliveryStatus
    {
        return match ($code) {
            2       => self::Delivered,
            4       => self::Discarded,
            1       => self::Pending,
            3       => self::Failed,
            0       => self::Send,
            default => self::Unknown, // Return Unknown as default if code is invalid
        };
    }

    case Delivered = 2;
    case Discarded = 4;
    case Pending   = 1;
    case Failed    = 3;
    case Send      = 0;
    case Unknown   = 99; // Default status for unknown codes
}
