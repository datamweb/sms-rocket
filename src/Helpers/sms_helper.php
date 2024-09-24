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

use Datamweb\SMSRocket\Services\SMSRocketService;

if (! function_exists('sms')) {
    function sms()
    {
        /** @var SMSRocketService */
        $smsRocket = service('smsRocket');
        
        return $smsRocket;
    }
}
