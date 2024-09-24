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
use Datamweb\SMSRocket\Enums\Farazsms\DeliveryStatus;
use Datamweb\SMSRocket\Exceptions\SMSException;
use Datamweb\SMSRocket\Models\SMSLogModel;
use Exception;

/**
 * Class FarazsmsDriver
 *
 * A simple implementation of the SMSDriverInterface.
 * This class provides basic functionality to send SMS messages
 * and retrieve their statuses.
 *
 * @see \Tests\Datamweb\SMSRocket\Drivers\FarazsmsDriverTest
 */
class FarazsmsDriver implements SMSDriverInterface
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
     * @see https://docs.ippanel.com/
     */
    public function send(string $recipient, string $message, string $sender): string
    {
        // Define parameters as an array
        $postFields = [
            'message'      => $message,
            'sender'       => $sender,
            'recipient'    => [$recipient],
            'sending_type' => 'webservice',
            // 'time' => Time::now('Asia/Tehran')->addMinutes(61)->toDateTime()->format('Y-m-d\TH:i:s.v\Z'),
        ];

        $baseUrl = 'https://api2.ippanel.com/api/v1/sms/send/webservice/single';

        try {
            // Send GET request
            $response = $this->client->request('POST', $baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'apiKey'       => $this->config['api_key'],
                ],
                'json'        => $postFields,
                'http_errors' => false,
            ]);

            // Check if the response is successful
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());
                // Check response status and return MessageID
                if (isset($responseData->status) && $responseData->status === 'OK') {
                    $messageId = (string) $responseData->data->message_id;

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
     *
     * @see https://docs.ippanel.com/
     */
    public function getDeliveryStatus(string $messageId): string
    {
        // Build URL using http_build_query
        $baseUrl = "https://api2.ippanel.com/api/v1/sms/message/show-recipient/message-id/{$messageId}";

        try {
            // Send GET request
            $response = $this->client->request('GET', $baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'apiKey'       => $this->config['api_key'],
                ],
                'http_errors' => false,
            ]);

            // Check response status
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());
                // Check response status and return delivery status
                if (isset($responseData->status) && $responseData->status === 'OK') {
                    $status = isset($responseData->data->deliveries[0]) ? $responseData->data->deliveries[0]->status : 99;

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
     * @see https://docs.ippanel.com/
     *
     * @return string A unique identifier for the sent message.
     *
     * @throws Exception    If an HTTP request error occurs.
     * @throws SMSException If the API returns an unsuccessful status or there is an error sending the SMS.
     */
    public function sendPatterned(string $recipient, string $patternCode, array $patternValues): string
    {
        // Define parameters as an array
        $postFields = [
            'recipient' => $recipient,
            'code'      => $patternCode, // $templateId,
            'variable'  => $patternValues,
            // Is hardcoded because the provider offers this number as a shared line.
            // The system will automatically replace it with the most stable line available.s
            'sender' => '3000505',
        ];

        $baseUrl = 'https://api2.ippanel.com/api/v1/sms/pattern/normal/send';

        try {
            // Send POST request
            $response = $this->client->request('POST', $baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'apikey'       => $this->config['api_key'],
                ],
                'json'        => $postFields,
                'http_errors' => false,
            ]);
            // Check if the response is successful
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());

                // Verify the status of the response and return the result
                if (isset($responseData->status) && $responseData->status === 'OK') {
                    $messageId = (string) $responseData->data->message_id;
                    // DB Record
                    $this->model->logSMS(self::class, $messageId, $recipient, 8, null, $patternCode, null);

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
     * @see https://docs.ippanel.com/
     */
    public function getCreditBalance(): float|int
    {
        $baseUrl = 'https://api2.ippanel.com/api/v1/sms/accounting/credit/show';

        try {
            // Send request to the API
            $response = $this->client->request('GET', $baseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'apikey'       => $this->config['api_key'],
                ],
                'http_errors' => false,
            ]);

            // Check if the response is successful
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode((string) $response->getBody());

                // Verify the status of the response and return the credit balance
                if (isset($responseData->status) && $responseData->status === 'OK') {
                    return $responseData->data->credit ?? 0;  // Default to 0 if data is missing
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
