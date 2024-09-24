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

namespace Datamweb\SMSRocket\Enums\Idehpardazan;

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
        return lang('Idehpardazan.' . $this->name);
    }

    /**
     * Retrieve the DeliveryStatus enum based on the provided code.
     *
     * @param int $code The code to look up.
     *
     * @return DeliveryStatus The matching status enum or the default Unknown status if not found.
     */
    public static function fromCode(int|string $code): DeliveryStatus
    {
        return match ($code) {
            1       => self::Recieved,
            2       => self::NotRecievedPhone,
            3       => self::RecievedToTci,
            4       => self::NotRecievedToTci,
            5       => self::RecievedToOperator,
            6       => self::Failed,
            7       => self::BlackList,
            8       => self::Unknown,
            default => self::Unknown, // Return Unknown as default if code is invalid
        };
    }

    case Recieved           = 1;
    case NotRecievedPhone   = 2;
    case RecievedToTci      = 3;
    case NotRecievedToTci   = 4;
    case RecievedToOperator = 5;
    case Failed             = 6;
    case BlackList          = 7;
    case Unknown            = 8;
}
