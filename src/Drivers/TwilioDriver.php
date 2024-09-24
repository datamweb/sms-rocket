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

use BadMethodCallException;
use CodeIgniter\HTTP\CURLRequest;
use Datamweb\SMSRocket\Enums\Twilio\DeliveryStatus;
use Datamweb\SMSRocket\Exceptions\SMSException;
use Datamweb\SMSRocket\Models\SMSLogModel;
use Exception;

/**
 * Class TwilioDriver
 *
 * A simple implementation of the SMSDriverInterface.
 * This class provides basic functionality to send SMS messages
 * and retrieve their statuses.
 */
class TwilioDriver implements SMSDriverInterface
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
     * @see https://www.twilio.com/docs/messaging/api/message-resource#create-a-message-resource
     */
    public function send(string $recipient, string $message, string $sender): string
    {
        // Define parameters as an array
        $params = [
            'From' => $sender,
            // 'MessagingServiceSid' => $this->config['messagingServiceSid'],
            'To'   => $recipient,
            'Body' => $message,
        ];

        $accountSID = $this->config['AccountSID'];
        $authToken  = $this->config['AuthToken'];

        // Build URL
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSID}/Messages.json";

        try {
            // Send GET request
            $response = $this->client->request('POST', $url, [
                'auth'        => [$accountSID, $authToken, 'basic'],
                'form_params' => $params,
                'http_errors' => false,
            ]);

            if ($response->getStatusCode() === 201) {
                $responseData = json_decode((string) $response->getBody());

                // Check response status and return MessageID
                if (isset($responseData->sid)) {
                    $messageId = (string) $responseData->sid;

                    $deliveryStatus = $responseData->status;
                    $numericCode    = DeliveryStatus::fromCode($deliveryStatus)->toNumericCode();

                    // DB Recording
                    $this->model->logSMS(self::class, $messageId, $recipient, $numericCode, $message, null, $sender);

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
     * @see https://www.twilio.com/docs/messaging/api/message-resource#fetch-a-message-resource
     */
    public function getDeliveryStatus(string $messageId): string
    {
        // Define parameters as an array

        $accountSID = $this->config['AccountSID'];
        $authToken  = $this->config['AuthToken'];

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSID}/Messages/{$messageId}.json";

        try {
            // Send GET request
            $response = $this->client->request('GET', $url, [
                'auth'        => [$accountSID, $authToken, 'basic'],
                'http_errors' => false,
            ]);

            // Check response status
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());

                // Check response status and return delivery status
                if (isset($responseData->status)) {
                    $deliveryStatus = $responseData->status;
                    $statusEnum     = DeliveryStatus::fromCode($deliveryStatus);
                    $numericCode    = $statusEnum->toNumericCode();

                    $this->model->updateStatus($messageId, $numericCode);

                    return DeliveryStatus::fromNumericCode($numericCode)->title();
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
     * @see NOT Support
     */
    public function sendPatterned(string $recipient, string $patternCode, array $patternValues): string
    {
        throw new BadMethodCallException("Driver {$this->config['driver_name']} does not support the send method.");
    }

    /**
     * Retrieves the SMS credit balance from the AmootSms.com API.
     *
     * @return float|int Returns the credit balance as a float or int. If an error occurs, it returns 0.
     *
     * @throws Exception    If an HTTP request error occurs.
     * @throws SMSException If the API returns an unsuccessful status or there is an error retrieving the credit balance.
     * @see https://help.twilio.com/articles/360025294494
     */
    public function getCreditBalance(): float|int
    {
        $accountSID = $this->config['AccountSID'];
        $authToken  = $this->config['AuthToken'];

        // Build URL using http_build_query
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSID}/Balance.json";

        try {
            // Send request to the API
            $response = $this->client->request('GET', $url, [
                'auth'        => [$accountSID, $authToken, 'basic'],
                'http_errors' => false,
            ]);

            // Check if the response is successful
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());

                // Verify the status of the response and return the credit balance
                if (isset($responseData->balance)) {
                    return (float) ($responseData->balance);
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
