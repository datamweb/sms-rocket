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

namespace Datamweb\SMSRocket\Services;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\Log\Logger;
use CodeIgniter\Shield\Entities\User;
use Datamweb\SMSRocket\Config\SMSRocketConfig;
use Datamweb\SMSRocket\Drivers\SMSDriverInterface;
use Datamweb\SMSRocket\Models\SMSLogModel;
use Datamweb\SMSRocket\Responses\SMSMultiResponse;
use Datamweb\SMSRocket\Responses\SMSResponse;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class SMSRocketService
 *
 * Service for sending SMS using different drivers, with caching and bulk message support.
 * This class manages SMS sending operations, handles driver configurations,
 * and facilitates responses for single and multiple recipients.
 */
class SMSRocketService
{
    protected ?SMSDriverInterface $driver = null;
    protected array $receivers            = [];
    protected ?string $sender             = null;
    protected ?string $message            = null;
    protected ?array $patternData         = null;
    protected ?string $patternCode        = null;

    /**
     * Constructor for the SMSRocketService.
     *
     * @param CacheInterface  $cache    The cache handler to use for caching responses.
     * @param SMSRocketConfig $config   The configuration settings for the SMS service.
     * @param Logger          $logger   The logger service to use for logging messages.
     * @param int             $cacheTtl Time-to-live for cached responses in seconds.
     */
    public function __construct(protected CacheInterface $cache, protected SMSRocketConfig $config, protected ?SMSLogModel $model, protected Logger $logger, protected CURLRequest $client, protected int $cacheTtl = 60)
    {
    }

    /**
     * Set the SMS driver to use.
     *
     * @param string|null $driverName The name of the driver to use. If null, the default driver is used.
     *
     * @return self Returns the current instance for method chaining.
     *
     * @throws InvalidArgumentException If the specified driver is not found or is unavailable.
     */
    public function driver(?string $driverName = null): self
    {
        $driverName ??= $this->config->defaultDriver;

        if (! isset($this->config->drivers[$driverName])) {
            $this->logger->error("Driver '{$driverName}' not found in configuration.");

            throw new InvalidArgumentException("Driver '{$driverName}' not found in configuration.");
        }

        $driverInfo   = $this->config->drivers[$driverName];
        $driverClass  = $driverInfo['class'];
        $driverConfig = $driverInfo['config'];

        if (isset($driverConfig['isAvailable']) && $driverConfig['isAvailable'] === false) {
            $this->logger->warning("Driver '{$driverName}' is not available.");

            throw new InvalidArgumentException("Driver '{$driverName}' is not available.");
        }

        $this->driver = new $driverClass($driverConfig, $this->model, $this->client);

        return $this;
    }

    /**
     * Set the sender's identifier.
     *
     * @param string $sender The sender's identifier (e.g., phone number).
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Set the message to be sent.
     *
     * @param string $message The message content.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set the receiver(s) for the SMS.
     *
     * @param array|string|User $to The recipient's phone number(s).
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setReceiver(array|string|User $to): self
    {
        if (is_array($to)) {
            foreach ($to as $recipient) {
                if ($recipient instanceof User) {
                    $phoneField = $this->config->phoneField;

                    if (! isset($recipient->{$phoneField})) {
                        $this->logger->error("The provided User does not have a {$phoneField} field.");

                        throw new InvalidArgumentException("The provided User does not have a {$phoneField} field.");
                    }

                    $this->receivers[] = $recipient->{$phoneField};
                } else {
                    $this->receivers[] = $recipient;
                }
            }
        } elseif ($to instanceof User) {
            $phoneField = $this->config->phoneField;

            if (! isset($to->{$phoneField})) {
                $this->logger->error("The provided User does not have a {$phoneField} field.");

                throw new InvalidArgumentException("The provided User does not have a {$phoneField} field.");
            }

            $this->receivers = [$to->{$phoneField}];
        } else {
            $this->receivers = [$to];
        }

        return $this;
    }

    /**
     * Send an SMS message to the specified recipients.
     *
     * @param array|string|User|null $to      The recipient(s) phone number(s). If null, uses previously set receivers.
     * @param string|null            $message The message content. If null, uses the previously set message.
     *
     * @return SMSMultiResponse An object containing responses for each recipient.
     *
     * @throws InvalidArgumentException If no receivers or message are set.
     */
    public function send(array|string|User|null $to = null, ?string $message = null): SMSMultiResponse
    {
        if ($to !== null) {
            $this->setReceiver($to);
        }

        if ($message !== null) {
            $this->setMessage($message);
        }

        if (empty($this->receivers)) {
            $this->logger->error('At least one receiver must be set.');

            throw new InvalidArgumentException('At least one receiver must be set.');
        }

        if (! isset($this->sender)) {
            $this->sender = $this->config->drivers[$this->config->defaultDriver]['config']['defaultSender'];
        }

        if (! isset($this->message) && $this->patternCode === null) {
            $this->logger->error('Message text is not set.');

            throw new InvalidArgumentException('Message text is not set.');
        }

        $multiResponse = new SMSMultiResponse();

        foreach ($this->receivers as $receiver) {
            if ($this->patternCode !== null) {
                $response = $this->sendPatternedSMS($receiver, $this->patternCode, $this->patternData);
            } else {
                $response = $this->sendWithRetry($receiver, $this->message);
            }
            $multiResponse->addResponse($receiver, $response);
        }

        $this->logger->info('SMS sent to recipients: ' . implode(', ', $this->receivers));

        return $multiResponse;
    }

    /**
     * Sends an SMS message to a specific receiver.
     *
     * @param string $receiver The recipient's phone number.
     * @param string $message  The message content.
     *
     * @return SMSResponse The response object containing the result of the operation.
     */
    private function sendToReceiver(string $receiver, string $message): SMSResponse
    {
        if ($this->driver === null) {
            $this->logger->error('No SMS driver is configured.');

            return new SMSResponse(false, 'No SMS driver is configured.', $receiver, null);
        }

        $cacheKey = $this->getCacheKey($receiver, $message);

        if ($result = $this->cache->get($cacheKey)) {
            $this->logger->info("SMS to {$receiver} retrieved from cache.");

            return new SMSResponse(true, $result, $receiver);
        }

        try {
            $messageId = $this->driver->send($receiver, $message, $this->sender);
            $this->cache->save($cacheKey, $messageId, $this->cacheTtl);

            $this->logger->info("The SMS was successfully forwarded, and Message ID {$messageId} has been received from the service provider.");

            return new SMSResponse(true, "The SMS was successfully forwarded, and Message ID {$messageId} has been received from the service provider.", $receiver, $messageId);
        } catch (Exception $e) {
            $this->logger->error("Failed to send SMS to {$receiver}: " . $e->getMessage());

            return new SMSResponse(false, 'Failed to send message: ' . $e->getMessage(), $receiver);
        }
    }

    /**
     * Generates a cache key for a specific recipient and message.
     *
     * @param string $to      The recipient's phone number.
     * @param string $message The message content.
     *
     * @return string A unique cache key based on recipient and message.
     */
    private function getCacheKey(string $to, string $message): string
    {
        return md5($to . $message);
    }

    /**
     * Retrieves the status of a sent SMS message.
     *
     * @param string $messageId The unique identifier of the sent message.
     *
     * @return SMSResponse The response object containing the status of the message.
     */
    public function getDeliveryStatus(string $messageId): SMSResponse
    {
        if ($this->driver === null) {
            $this->driver();
        }

        $cacheKey = "sms_status_{$messageId}";

        if ($result = $this->cache->get($cacheKey)) {
            $this->logger->info("SMS status for message ID {$messageId} retrieved from cache.");

            return new SMSResponse(true, $result, $messageId, $messageId);
        }

        $status = $this->driver->getDeliveryStatus($messageId);

        $this->cache->save($cacheKey, $status, 3600);
        $this->logger->info("SMS status for message ID {$messageId} retrieved from driver.");

        return new SMSResponse(true, $status, $messageId, $messageId);
    }

    /**
     * Attempt to send SMS with retry logic.
     */
    private function sendWithRetry(string $recipient, string $message): SMSResponse
    {
        $retries = $this->config->retryAttempts;

        for ($attempt = 0; $attempt < $retries; $attempt++) {
            $response = $this->sendToReceiver($recipient, $message);
            if ($response->isOK()) {
                return $response;
            }

            $this->logger->warning(sprintf('Attempt %d failed for recipient: %s', $attempt + 1, $recipient));
            sleep($this->config->retryDelay);
        }

        return new SMSResponse(false, "Failed after {$retries} attempts.", $recipient, null);
    }

    /**
     * Retrieves the current SMS credit balance from the SMS driver.
     *
     * This method interacts with the SMS driver to fetch the remaining balance
     * of SMS credits available for sending messages.
     *
     * @return float|int|string The credit balance returned from the SMS driver. The type may vary depending on the driver.
     *
     * @throws InvalidArgumentException If no SMS driver is configured or if the driver does not support credit retrieval.
     */
    public function getCredit(): float|int|string
    {
        if ($this->driver === null) {
            $this->driver();

            throw new InvalidArgumentException('No SMS driver is configured.');
        }

        try {
            $credit = $this->driver->getCreditBalance();
            //  return new SMSResponse(true, $status, $messageId);
            $this->logger->info('Credit retrieved successfully: ' . $credit);

            return $credit;
        } catch (Exception $e) {
            $this->logger->error('Failed to retrieve credit: ' . $e->getMessage());

            throw new RuntimeException('Failed to retrieve credit: ' . $e->getMessage());
        }
    }

    /**
     * Set the pattern code for patterned SMS.
     */
    public function setPattern(string $patternCode): self
    {
        $this->patternCode = $patternCode;

        return $this;
    }

    /**
     * Set the pattern data for patterned SMS.
     */
    public function setPatternData(array $patternData): self
    {
        $this->patternData = $patternData;

        return $this;
    }

    private function sendPatternedSMS(string $receiver, string $patternCode, array $patternData): SMSResponse
    {
        try {
            $messageId = $this->driver->sendPatterned($receiver, $patternCode, $patternData);
            $this->logger->info("Patterned SMS sent to {$receiver} with Message ID: {$messageId}");

            return new SMSResponse(true, "Patterned Message ID {$messageId} delivered successfully.", $receiver, $messageId);
        } catch (Exception $e) {
            $this->logger->error("Failed to send patterned SMS to {$receiver}: " . $e->getMessage());

            return new SMSResponse(false, 'Failed to send patterned message: ' . $e->getMessage(), $receiver);
        }
    }
}
