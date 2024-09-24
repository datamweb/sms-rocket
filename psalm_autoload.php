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

require __DIR__ . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

$helperDirs = [
    'vendor/codeigniter4/shield/src/Helpers',
    'vendor/codeigniter4/settings/src/Helpers',
    'vendor/codeigniter4/framework/system/Helpers',
    'src/Helpers',
];

foreach ($helperDirs as $dir) {
    $dir = __DIR__ . '/' . $dir;
    if (! is_dir($dir)) {
        continue;
    }

    chdir($dir);

    foreach (glob('*_helper.php') as $filename) {
        $filePath = realpath($dir . '/' . $filename);

        require_once $filePath;
    }
}

chdir(__DIR__);
