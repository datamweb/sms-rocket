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
use Datamweb\SMSRocket\Enums\Amootsms\DeliveryStatus;
use Datamweb\SMSRocket\Exceptions\SMSException;
use Datamweb\SMSRocket\Models\SMSLogModel;
use Exception;

/**
 * Class AmootsmsDriver
 *
 * A simple implementation of the SMSDriverInterface.
 * This class provides basic functionality to send SMS messages
 * and retrieve their statuses.
 */
class AmootsmsDriver implements SMSDriverInterface
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
        $params = [
            'Token'          => $this->config['token'],
            'SendDateTime'   => Time::now('Asia/Tehran')->addHours(2)->toDateTime()->format('c'),
            'SMSMessageText' => $message,
            'LineNumber'     => $sender,
            'Mobiles'        => $recipient,
        ];

        // Build URL using http_build_query
        $baseUrl = 'https://portal.amootsms.com/rest/SendSimple';
        $url     = $baseUrl . '?' . http_build_query($params);

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
     * @see https://doc.amootsms.com/documentation/api/rest/getdelivery
     */
    public function getDeliveryStatus(string $messageId): string
    {
        // Define parameters as an array
        $params = [
            'Token'     => $this->config['token'],
            'MessageID' => $messageId,
        ];

        // Build URL using http_build_query
        $baseUrl = 'https://portal.amootsms.com/rest/GetDelivery';
        $url     = $baseUrl . '?' . http_build_query($params);

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
     * @see https://doc.amootsms.com/documentation/api/rest/sendwithpattern
     */
    public function sendPatterned(string $recipient, string $patternCode, array $patternValues): string
    {
        // Define parameters as an array
        $params = [
            'Token'         => $this->config['token'],
            'Mobile'        => $recipient,
            'PatternCodeID' => $patternCode,
            'PatternValues' => implode(',', $patternValues),
        ];

        // Build URL using http_build_query
        $baseUrl = 'https://portal.amootsms.com/rest/SendWithPattern';
        $url     = $baseUrl . '?' . http_build_query($params);

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
     * @see https://doc.amootsms.com/documentation/api/rest/accountstatus
     */
    public function getCreditBalance(): float|int
    {
        // Define parameters as an array
        $params = [
            'Token' => $this->config['token'],
        ];

        // Build URL using http_build_query
        $baseUrl = 'https://portal.amootsms.com/rest/AccountStatus';
        $url     = $baseUrl . '?' . http_build_query($params);

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
