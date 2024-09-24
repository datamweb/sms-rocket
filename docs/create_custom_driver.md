# Creating a Custom SMS Driver for SMSRocket

**SMSRocket** is designed to be flexible and extensible, allowing you to create your own SMS drivers tailored to your specific needs. This guide will walk you through the steps to create a custom SMS driver.

## Define the Driver Interface

All SMS drivers must implement the `Datamweb\SMSRocket\Drivers\SMSDriverInterface`. This interface ensures that your driver adheres to the required structure for sending SMS messages and handling responses.

### Example Interface Definition

```php
namespace Datamweb\SMSRocket\Drivers;

interface SMSDriverInterface
{
    public function send(string $recipient, string $message, string $sender): string;
    public function getDeliveryStatus(string $messageId): string;
    public function getCreditBalance(): float|int;
    public function sendPatterned(string $recipient, string $patternCode, array $patternValues): string;
}
```

## Create Your Custom Driver Class

Now, create your custom driver class implementing the `Datamweb\SMSRocket\Drivers\SMSDriverInterface`. You can place this class in the `Drivers` directory of **App\SMSRocket\Drivers**.

### Example Custom Driver

```php
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

namespace App\SMSRocket\Drivers;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\I18n\Time;
use Datamweb\SMSRocket\Enums\CustomSMSDriver\DeliveryStatus;
use Datamweb\SMSRocket\Exceptions\SMSException;
use Datamweb\SMSRocket\Models\SMSLogModel;
use Exception;

/**
 * Class CustomSMSDriver
 *
 * A simple implementation of the SMSDriverInterface.
 * This class provides basic functionality to send SMS messages
 * and retrieve their statuses.
 */
class CustomSMSDriver implements SMSDriverInterface
{
    protected array $config;

    /**
     * Constructor for the ExampleDriver.
     *
     * Initializes the driver with the provided configuration.
     *
     * @param array $config Optional configuration settings for the driver.
     *                      This can include API keys, sender information, etc.
     */
    public function __construct(array $config, protected SMSLogModel $model, protected CURLRequest $client)
    {
        // Initialize with config if necessary
        $this->config = array_merge($config, ['driver_name' => self::class]);
    }

    /**
     * Sends an SMS message to the specified recipient.
     *
     * @param string $recipient The phone number of the recipient.
     * @param string $message   The content of the message to be sent.
     *
     * @return string A unique identifier for the sent message.
     *                This ID can be used to track the status of the message.
     *
     * @throws Exception    If an HTTP request error occurs.
     * @throws SMSException If the API returns an unsuccessful status or there is an error sending the SMS.
     * @see api docs
     */
    public function send(string $recipient, string $message, string $sender): string
    {

        $baseUrl = 'https://...';

        try {
            // Send GET request
            $response = $this->client->request('GET', $url, [
                'http_errors' => false,
            ]);
            // Check if the response is successful
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());
                // Check response status and return MessageID
                if (isset($responseData->Status) && $responseData->Status === 'Success') {
                    $messageId = (string) $responseData->Data[0]->MessageID;

                    // DB Recording
                    $this->model->logSMS(self::class, $messageId, $recipient, 100, $message, null, $sender);

                    return $messageId;
                }

                throw SMSException::forUnsuccessfulAPIStatus();
            }

            throw SMSException::forFailedAPIResponse();
        } catch (Exception $e) {
            // Log the error and throw an exception
            log_message('error', $e->getMessage());

            throw SMSException::forErrorSendingSimpleSMS($e->getMessage());
        }
    }

    /**
     * Retrieves the delivery status from the AmootSMS.com API.
     *
     * @param string $messageId The ID of the message for which to retrieve the delivery status.
     *
     * @return string Returns the delivery status. If an error occurs, it returns an error message.
     *
     * @throws Exception    If an HTTP request error occurs.
     * @throws SMSException If the API returns an unsuccessful status or there is an error retrieving the delivery status.
     *
     * @see api docss
     */
    public function getDeliveryStatus(string $messageId): string
    {

        // Build URL using http_build_query
        $url = 'https://....';

        try {
            // Send GET request
            $response = $this->client->request('GET', $url, [
                'http_errors' => false,
            ]);

            // Check response status
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());

                // Check response status and return delivery status
                if (isset($responseData->Status) && $responseData->Status === 'Success') {
                    $deliveryStatus = $responseData->Data->DeliveryType;

                    $this->model->updateStatus($messageId, $deliveryStatus);

                    return DeliveryStatus::getTitleFromCode($deliveryStatus);
                }

                throw SMSException::forUnsuccessfulAPIStatus();
            }

            throw SMSException::forFailedAPIResponse();
        } catch (Exception $e) {
            // Log the error and throw an exception
            log_message('error', $e->getMessage());

            throw SMSException::forFailedDeliveryStatus($e->getMessage());
        }
    }

    /**
     * Sends a patterned SMS message to the specified recipient.
     *
     * @param string $recipient     The phone number of the recipient.
     * @param string $patternCode   The pattern code for the SMS.
     * @param array  $patternValues The values to replace in the pattern.
     *
     * @return string A unique identifier for the sent message.
     *
     * @throws Exception    If an HTTP request error occurs.
     * @throws SMSException If the API returns an unsuccessful status or there is an error sending the SMS.
     * @see api docs
     */
    public function sendPatterned(string $recipient, string $patternCode, array $patternValues): string
    {

        $url = 'https://...';

        try {
            // Send GET request
            $response = $this->client->request('GET', $url, [
                'http_errors' => false,
            ]);
            // Check if the response is successful
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());

                // Verify the status of the response and return the result
                if (isset($responseData->Status) && $responseData->Status === 'Success') {
                    $messageId = (string) $responseData->Data[0]->MessageID;

                    // DB Record
                    $this->model->logSMS(self::class, $messageId, $recipient, 100, null, $patternCode, null);

                    return $messageId;
                }

                throw SMSException::forUnsuccessfulAPIStatus();
            }

            throw SMSException::forFailedAPIResponse();
        } catch (Exception $e) {
            // Log the error and throw an exception
            log_message('error', $e->getMessage());

            throw SMSException::forErrorSendingPatternedSMS($e->getMessage());
        }
    }

    /**
     * Retrieves the SMS credit balance from the AmootSms.com API.
     *
     * @return float|int Returns the credit balance as a float or int. If an error occurs, it returns 0.
     *
     * @throws Exception    If an HTTP request error occurs.
     * @throws SMSException If the API returns an unsuccessful status or there is an error retrieving the credit balance.
     * @see api docs
     */
    public function getCreditBalance(): float|int
    {

        // Build URL
        $url = 'https://...';

        try {
            // Send request to the API
            $response = $this->client->request('GET', $url, [
                'http_errors' => false,
            ]);

            // Check if the response is successful
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());

                // Verify the status of the response and return the credit balance
                if (isset($responseData->Status) && $responseData->Status === 'Success') {
                    return $responseData->RemaindCredit ?? 0;  // Default to 0 if data is missing
                }

                throw SMSException::forUnsuccessfulAPIStatus();
            }

            throw SMSException::forFailedAPIResponse();
        } catch (Exception $e) {
            // Log the exception and rethrow it
            log_message('error', $e->getMessage());

            throw SMSException::forErrorRetrievingCreditBalance($e->getMessage());
        }
    }
}
```

## Register Your Custom Driver

After creating your custom driver, you need to register it in the **App\Config\SMSRocketConfig.php** so it can be used within the service.

### Example Configuration

In your confing file (**App\Config\SMSRocketConfig.php**), add your custom driver:

```php
<?php

declare(strict_types=1);

namespace Config;

use Datamweb\SMSRocket\Config\SMSRocketConfig as OriginalSMSRocketConfig;
use App\SMSRocket\Drivers\CustomSMSDriver;

class SMSRocketConfig extends OriginalSMSRocketConfig
{
    /**
     * Constructor for SMSRocketConfig to load environment variables.
     */
    public function __construct()
    {
        parent::__construct();

        $this->drivers['custom'] = [
            'class' => CustomSMSDriver::class,
            'config' => [
                'api_key'       => env('CUSTOM_SMS_DRIVER_API_KEY', 'your-api-key'),
                'defaultSender' => env('CUSTOM_SMS_DRIVER_SMS_SENDER', '3000XXXX'),
                'isAvailable'   => true,
            ],
        ];
        // ... other drivers ...
    }
}

```

## Use Your Custom Driver

Now that your custom driver is registered, you can use it just like any other driver in the **smsRocket** service.

### Example Usage

```php
<?php

use Datamweb\SMSRocket\Services\SMSRocketService;

/** @var SMSRocketService $smsService */
$smsService = service('smsRocket');

// Send an SMS using the custom driver
$response = $smsService->driver('custom')->setSender('YourSenderID')->setMessage('Hello, Custom World!')->send('1234567890');

if ($response->isOK()) {
    echo "SMS sent successfully with Message ID: " . $response->getMessage();
} else {
    echo "Failed to send SMS: " . $response->getMessage();
}
```

Congratulations! You have successfully created a custom SMS driver for SMSRocket. This allows you to integrate any SMS service provider of your choice into your application.