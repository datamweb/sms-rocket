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

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\I18n\Time;
use Datamweb\SMSRocket\Enums\Idehpardazan\DeliveryStatus;
use Datamweb\SMSRocket\Exceptions\SMSException;
use Datamweb\SMSRocket\Models\SMSLogModel;
use Exception;

/**
 * Class IdehpardazanDriver
 *
 * A simple implementation of the SMSDriverInterface.
 * This class provides basic functionality to send SMS messages
 * and retrieve their statuses.
 */
class IdehpardazanDriver implements SMSDriverInterface
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
     * @see https://doc.amootsms.com/documentation/api/rest/sendsimple
     */
    public function send(string $recipient, string $message, string $sender): string
    {
        // Define parameters as an array
        $postFields = [
            'MessageText' => $message,
            'LineNumber'  => $sender,
            'Mobiles'     => [$recipient],
            // 'SendDateTime' => Time::now('Asia/Tehran')->addMinutes(61)->getTimestamp(),
        ];

        $baseUrl = 'https://api.sms.ir/v1/send/bulk';

        try {
            // Send GET request
            $response = $this->client->request('POST', $baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-API-KEY'    => $this->config['api_key'],
                ],
                'json'        => $postFields,
                'http_errors' => false,
            ]);
            // Check if the response is successful
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());
                // Check response status and return MessageID
                if (isset($responseData->status) && $responseData->status === 1) {
                    $messageId = (string) $responseData->data->messageIds[0];

                    // DB Recording
                    $this->model->logSMS(self::class, $messageId, $recipient, 0, $message, null, $sender);

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
     * @see https://doc.amootsms.com/documentation/api/rest/getdelivery
     */
    public function getDeliveryStatus(string $messageId): string
    {
        // Build URL using http_build_query
        $baseUrl = "https://api.sms.ir/v1/send/{$messageId}";

        try {
            // Send GET request
            $response = $this->client->request('GET', $baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-API-KEY'    => $this->config['api_key'],
                ],
                'http_errors' => false,
            ]);

            // Check response status
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());

                // Check response status and return delivery status
                if (isset($responseData->status) && $responseData->status === 1) {
                    $status = $responseData->data->deliveryState;

                    $this->model->updateStatus($messageId, $status);

                    return DeliveryStatus::getTitleFromCode($status);
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
     * @see https://app.sms.ir/developer/help/verify
     *
     * @return string A unique identifier for the sent message.
     *
     * @throws Exception    If an HTTP request error occurs.
     * @throws SMSException If the API returns an unsuccessful status or there is an error sending the SMS.
     */
    public function sendPatterned(string $recipient, string $patternCode, array $patternValues): string
    {
        $patternValues = array_map(static fn ($key, $value) => [
            'name'  => $key,
            'value' => $value,
        ], array_keys($patternValues), $patternValues);

        // Define parameters as an array
        $postFields = [
            'mobile'     => $recipient,
            'templateId' => $patternCode, // $templateId,
            'parameters' => $patternValues,
        ];

        $baseUrl = 'https://api.sms.ir/v1/send/verify';

        try {
            // Send POST request
            $response = $this->client->request('POST', $baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-API-KEY'    => $this->config['api_key'],
                ],
                'json'        => $postFields,
                'http_errors' => false,
            ]);
            // Check if the response is successful
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());

                // Verify the status of the response and return the result
                if (isset($responseData->status) && $responseData->status === 1) {
                    $messageId = (string) $responseData->data->messageId;
                    // DB Record
                    $this->model->logSMS(self::class, $messageId, $recipient, 0, null, $patternCode, null);

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
     * @see https://doc.amootsms.com/documentation/api/rest/accountstatus
     */
    public function getCreditBalance(): float|int
    {
        $baseUrl = 'https://api.sms.ir/v1/credit';

        try {
            // Send request to the API
            $response = $this->client->request('GET', $baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-API-KEY'    => $this->config['api_key'],
                ],
                'http_errors' => false,
            ]);

            // Check if the response is successful
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());

                // Verify the status of the response and return the credit balance
                if (isset($responseData->status) && $responseData->status === 1) {
                    return $responseData->data ?? 0;  // Default to 0 if data is missing
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
