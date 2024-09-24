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

namespace Datamweb\SMSRocket\Traits;

use Datamweb\SMSRocket\Config\SMSRocketConfig;

trait ObfuscatesSensitiveDataTrait
{
    /**
     * Public method to check and obfuscate sensitive data in the message.
     *
     * This method scans the message for sensitive data patterns defined in the configuration file (e.g., credit card numbers, national IDs).
     * When a pattern match is found, the sensitive data is replaced with masked values (e.g., `**** **** **** ****`).
     *
     * @param string     $message        The message text that may contain sensitive data.
     * @param array|null $customPatterns Optional custom patterns to match and obfuscate sensitive data. If not provided, it will use default patterns from the config.
     *
     * @return string Returns the obfuscated message with sensitive data masked.
     */
    public function hideSensitive(string $message, ?array $customPatterns = null): string
    {
        /** @var SMSRocketConfig $config */
        $config = config('SMSRocketConfig');

        // Load sensitive data patterns from the config file if no custom patterns are provided
        $patterns = $customPatterns ?? $config->patterns;

        // Iterate through each pattern and replace sensitive data with a masked version
        foreach ($patterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, (string) $message);
        }

        return $message;
    }
}
