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

namespace Datamweb\SMSRocket\Responses;

use Stringable;

/**
 * Class SMSMultiResponse
 *
 * Collects and manages multiple SMS responses.
 * This class allows for easy handling of responses for multiple SMS operations,
 * enabling retrieval of individual responses and checking overall success.
 */
class SMSMultiResponse implements Stringable
{
    /**
     * @var array<string, SMSResponse> An associative array mapping recipients to their SMS responses.
     */
    protected array $responses = [];

    /**
     * Adds an SMS response for a specific recipient.
     *
     * @param string      $recipient The recipient's phone number.
     * @param SMSResponse $response  The SMS response to add.
     */
    public function addResponse(string $recipient, SMSResponse $response): void
    {
        $this->responses[$recipient] = $response;
    }

    /**
     * Retrieves the SMS response for a specific recipient.
     *
     * @param string $recipient The recipient's phone number.
     *
     * @return SMSResponse|null The SMS response for the recipient, or null if not found.
     */
    public function getResponse(string $recipient): ?SMSResponse
    {
        return $this->responses[$recipient] ?? null;
    }

    /**
     * Gets all SMS responses.
     *
     * @return array<string, SMSResponse> An array of all SMS responses.
     */
    public function getAllResponses(): array
    {
        return $this->responses;
    }

    /**
     * Checks if all SMS operations to the provider were successful.
     *
     * This method iterates through all SMS responses from the provider and
     * checks if each operation was successful. It returns true only if
     * all responses indicate success, meaning that every SMS was sent
     * without any issues.
     *
     * @return bool True if all responses indicate success, false otherwise.
     */
    public function allOK(): bool
    {
        foreach ($this->responses as $response) {
            if (! $response->isOK()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converts the SMSMultiResponse object to a string representation.
     *
     * This method provides a summary of all responses, including recipients and their responses.
     *
     * @return string A formatted string representation of all SMS responses.
     */
    public function __toString(): string
    {
        $result = [];

        foreach ($this->responses as $recipient => $response) {
            $result[] = "Recipient: {$recipient}, Response: {$response}";
        }

        return implode("\n", $result);
    }
}
