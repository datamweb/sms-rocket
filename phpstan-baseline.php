<?php declare(strict_types = 1);

$ignoreErrors = [];

// $ignoreErrors[] = [
// 	'message' => '#^Access to an undefined property hasProperty\\(Status\\)\\:\\:\\$Data\\.$#',
// 	'count' => 3,
// 	'path' => __DIR__ . '/src/Drivers/AmootsmsDriver.php',
// ];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
