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

namespace Datamweb\SMSRocket\Enums\Amootsms;

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
        return lang('Amootsms.' . $this->name);
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
            0       => self::SendToTci,
            1       => self::RecievedPhone,
            2       => self::NotRecievedPhone,
            3       => self::TciError,
            5       => self::UnknownError,
            8       => self::TciReceived,
            16      => self::NotTciReceived,
            35      => self::BlackList,
            100     => self::Unknown,
            200     => self::Sent,
            300     => self::Filtered,
            400     => self::SendingList,
            500     => self::NoReceipt,
            501     => self::SendWithAvanak,
            502     => self::SendWithBackupVtel,
            900     => self::SendingQueue,
            950     => self::WrongNumber,
            951     => self::EmptyMessage,
            952     => self::ShortCodeInvalid,
            default => self::Unknown, // Return Unknown as default if code is invalid
        };
    }
    case SendToTci          = 0;
    case RecievedPhone      = 1;
    case NotRecievedPhone   = 2;
    case TciError           = 3;
    case UnknownError       = 5;
    case TciReceived        = 8;
    case NotTciReceived     = 16;
    case BlackList          = 35;
    case Unknown            = 100;
    case Sent               = 200;
    case Filtered           = 300;
    case SendingList        = 400;
    case NoReceipt          = 500;
    case SendWithAvanak     = 501;
    case SendWithBackupVtel = 502;
    case SendingQueue       = 900;
    case WrongNumber        = 950;
    case EmptyMessage       = 951;
    case ShortCodeInvalid   = 952;
}
