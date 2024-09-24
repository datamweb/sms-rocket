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

namespace Datamweb\SMSRocket\Config;

use App\SMSRocket\Drivers\ExampleDriver;
use CodeIgniter\Config\BaseConfig;
use Datamweb\SMSRocket\Drivers\AmootsmsDriver;
use Datamweb\SMSRocket\Drivers\FarazsmsDriver;
use Datamweb\SMSRocket\Drivers\IdehpardazanDriver;
use Datamweb\SMSRocket\Drivers\SMSDriverInterface;
use Datamweb\SMSRocket\Drivers\TwilioDriver;

class SMSRocketConfig extends BaseConfig
{
    /**
     * Associative array of driver configurations.
     *
     * @var array<string, array{
     *     class: class-string<SMSDriverInterface>,
     *     config: array<string, string|bool>
     * }>
     */
    public array $drivers;

    /**
     * Default driver name to be used if none is specified.
     */
    public string $defaultDriver = 'twilio';

    /**
     * Field name in the User model that holds the phone number.
     */
    public string $phoneField = 'phone';

    /**
     * Number of retry attempts for sending SMS.
     */
    public int $retryAttempts = 3;

    /**
     * Delay between retry attempts (in seconds).
     */
    public int $retryDelay = 1;

    /**
     * --------------------------------------------------------------------
     * Customize the DB group used for model
     * --------------------------------------------------------------------
     */
    public ?string $DBGroup = null;

    /**
     * --------------------------------------------------------------------
     * Customize Name of SMSRocket Table
     * --------------------------------------------------------------------
     * Only change if you want to rename the default SMSRocket table names
     *
     * It may be necessary to change the names of the table for
     * security reasons, to prevent the conflict of table names,
     * the internal policy of the companies or any other reason.
     */
    public string $table = 'sms_log';

    /**
     * --------------------------------------------------------------------
     * Tracking & Recording in DB
     * --------------------------------------------------------------------
     * By default, SMSRocket records the sent SMS in the database.
     * If you want to disable this feature, please set `false`.
     */
    public bool $enableDBLogging = true;

    /**
     * @var array An array of regex patterns used to identify and obfuscate sensitive data in SMS messages.
     *
     * This array holds regular expression (regex) patterns that match sensitive data, such as credit card numbers
     * or national ID numbers, and replaces them with a masked version. Each pattern is paired with its corresponding
     * replacement value to ensure that sensitive information is not exposed in SMS messages or when stored in the database.
     *
     * - The first pattern matches a 16-digit credit card number (optionally separated by spaces or dashes) and replaces
     *   it with `**** **** **** ****`.
     * - The second pattern matches a 10-digit national ID and replaces it with `**** **** **`.
     *
     * This is particularly useful for ensuring compliance with data protection regulations by masking sensitive information
     * before it is logged or sent via SMS.
     *
     * Example usage:
     *
     * If a message contains `Your card number is 1234 5678 9012 3456 and your National ID is 1234567890`,
     * it would be transformed into `Your card number is **** **** **** **** and your National ID is **** **** **`.
     */
    public array $patterns = [
        // Match credit card numbers (16 digits)
        '/\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b/' => '**** **** **** ****',

        // Match national ID numbers (10 digits)
        '/\b\d{10}\b/' => '**** **** **',
        '/\b\d{4}\b/'  => '****', // Match OPT code numbers (4 digits)
        '/\b\d{6}\b/'  => '******', // Match OPT code numbers (6 digits)
    ];

    /**
     * Enable or disable obfuscating sensitive data before saving to the database.
     * If true, sensitive data in the message will be filtered and obfuscated before being logged.
     */
    public bool $enableSensitiveDataFiltering = true;

    /**
     * Constructor for SMSRocketConfig to load environment variables.
     */
    public function __construct()
    {
        $this->drivers = [
            // 'example' => [
            //     'class'  => ExampleDriver::class,
            //     'config' => [
            //         'api_key'       => env('EXAMPLE_SMS_API_KEY', 'api key'),
            //         'api_secret'    => env('EXAMPLE_SMS_API_SECRET', 'default-api-secret'),
            //         'defaultSender' => 'default-sender-number',
            //         'isAvailable'   => true,
            //     ],
            // ],
            'twilio' => [
                'class'  => TwilioDriver::class,
                'config' => [
                    // https://console.twilio.com/us1/account/keys-credentials/api-keys
                    'AccountSID'          => env('TWILIO_SMS_ACCOUNT_SID', ''),
                    'AuthToken'           => env('TWILIO_SMS_AUTH_TOKEN', ''),
                    'defaultSender'       => env('TWILIO_SMS_SENDER', ''),
                    'messagingServiceSid' => env('TWILIO_SMS_MESSAGING_SERVICE_SID', ''), // required if From is not passed
                    'isAvailable'         => true,
                ],
            ],
            'farazsms' => [
                'class'  => FarazsmsDriver::class,
                'config' => [
                    'api_key'       => env('FARAZSMS_SMS_API_KEY', 'Enter your farazsms.com api key here'),
                    'defaultSender' => env('FARAZSMS_SMS_SENDER', ''),
                    'isAvailable'   => true,
                ],
            ],
            'smsir' => [
                'class'  => IdehpardazanDriver::class,
                'config' => [
                    'api_key'       => env('SMSIR_SMS_API_KEY', 'Enter your sms.ir api key here'),
                    'defaultSender' => env('SMSIR_SMS_SENDER', ''),
                    'isAvailable'   => true,
                ],
            ],
            'amootsms' => [
                'class'  => AmootsmsDriver::class,
                'config' => [
                    'token'         => env('AMOOTSMS_SMS_API_KEY', 'Enter your Amootsms.com token here'), // https://portal.amootsms.com/client/APIToken
                    'defaultSender' => env('AMOOTSMS_SMS_SENDER', 'public'),
                    'isAvailable'   => true,
                ],
            ],
        ];

        parent::__construct();
    }
}
