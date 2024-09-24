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

use CodeIgniter\Cache\CacheFactory;
use CodeIgniter\Config\BaseService;
use CodeIgniter\Log\Logger;
use Datamweb\SMSRocket\Models\SMSLogModel;
use Datamweb\SMSRocket\Services\SMSRocketService;
use InvalidArgumentException;

class Services extends BaseService
{
    /**
     * Retrieves an instance of the SMSRocket service.
     *
     * This method can return a shared instance of the SMSRocket service if requested,
     * or create a new instance if not. It also handles the configuration and cache setup.
     *
     * @param bool $getShared Indicates whether to return a shared instance.
     *                        If true, returns the shared instance; otherwise, creates a new one.
     *
     * @return SMSRocketService An instance of the SMSRocketService.
     *
     * @throws InvalidArgumentException If the cache configuration is invalid.
     */
    public static function smsRocket(bool $getShared = true): SMSRocketService
    {
        // If a shared instance is requested, use it
        if ($getShared) {
            return static::getSharedInstance('smsRocket');
        }

        // Create cache handler and configuration
        $cache = CacheFactory::getHandler(config('Cache'));

        /** @var SMSRocketConfig $config */
        $config = config('SMSRocketConfig');

        /** @var Logger $logger Create logger instance */
        $logger = service('logger');

        $client = service('curlrequest', [], null, null, true);

        /** @var SMSLogModel $model Create model instance */
        $model = model('SMSLogModel');

        // Create and return an instance of SMSRocketService
        return new SMSRocketService($cache, $config, $model, $logger, $client);
    }
}
