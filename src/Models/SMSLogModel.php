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

namespace Datamweb\SMSRocket\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use Datamweb\SMSRocket\Config\SMSRocketConfig;
use Datamweb\SMSRocket\Traits\ObfuscatesSensitiveDataTrait;
use Exception;

class SMSLogModel extends Model
{
    use ObfuscatesSensitiveDataTrait;

    protected SMSRocketConfig $smsRocketConfig;
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'provider_name',
        'from',
        'to',
        'message',
        'message_id',
        'template_id',
        'status',
        'response',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function __construct()
    {
        $this->smsRocketConfig = config('SMSRocketConfig');

        if ($this->smsRocketConfig->DBGroup !== null) {
            $this->DBGroup = $this->smsRocketConfig->DBGroup;
        }

        parent::__construct();
    }

    protected function initialize(): void
    {
        $this->table = $this->smsRocketConfig->table;
    }

    /**
     * Logs SMS details into the database.
     *
     * @param string      $messageId   The ID of the message.
     * @param string      $recipient   The recipient's phone number.
     * @param string|null $message     The message content.
     * @param string|null $template_id The pattern ID
     * @param string|null $sender      The sender's phone number.
     * @param int         $status      The initial status of the SMS (e.g., '100' for 'Sent').
     *
     * @return bool Returns true if logging is successful, false otherwise.
     */
    public function logSMS(string $provider_name, string $messageId, string $recipient, int $status, ?string $message = null, ?string $template_id = null, ?string $sender = null): bool
    {
        // Check if DB logging is enabled
        if (! $this->smsRocketConfig->enableDBLogging) {
            // Logging is disabled, skip saving to the database
            log_message('info', 'DB logging is disabled. Skipping message log.');

            return false;
        }

        // Check if sensitive data filtering is enabled in the config
        // Hide sensitive data in the message with patterns
        if ($this->smsRocketConfig->enableSensitiveDataFiltering && $message !== null) {
            $message = $this->hideSensitive($message);
        }

        try {
            // Create the data array
            $data = [
                'provider_name' => $provider_name,
                'message_id'    => $messageId,
                'to'            => $recipient,
                'message'       => $message,
                'template_id'   => $template_id,
                'from'          => $sender,
                'status'        => $status,
                'created_at'    => Time::now(),
            ];

            // Save the record
            return $this->save($data);
        } catch (Exception $e) {
            // Log the error
            log_message('error', 'Error logging SMS to DB: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Updates the delivery status of a sent SMS message.
     *
     * @param string     $messageId The ID of the message for which to update the status.
     * @param int|string $newStatus The new status to set for the message.
     *
     * @return bool Returns true if the status was successfully updated, false otherwise.
     */
    public function updateStatus(string $messageId, $newStatus): bool
    {
        try {
            // Find the SMS record by Message ID
            $smsRecord = $this->where('message_id', $messageId)->first();

            // Check if the record exists
            if ($smsRecord) {
                // Update the status
                $smsRecord->status = $newStatus;

                return $this->save($smsRecord);
            }

            // If the record doesn't exist, log and return false
            log_message('error', 'Message ID not found: ' . $messageId);

            return false;
        } catch (Exception $e) {
            // Log the error and return false
            log_message('error', 'Error updating SMS status: ' . $e->getMessage());

            return false;
        }
    }
}
