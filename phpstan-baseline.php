<?php declare(strict_types = 1);

$ignoreErrors = [];

$ignoreErrors[] = [
	'message' => '#^Access to an undefined property hasProperty\\(Status\\)\\:\\:\\$Data\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/src/Drivers/AmootsmsDriver.php',
];

$ignoreErrors[] = [
	'message' => '#^Access to an undefined property hasProperty\\(status\\)\\:\\:\\$data\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/src/Drivers/IdehpardazanDriver.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property hasProperty\\(status\\)\\:\\:\\$data\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Drivers/FarazsmsDriver.php',
];
$ignoreErrors[] = [
	'message' => '#Access to an undefined property hasProperty\(sid\)::\$status.#',
	'count' => 1,
	'path' => __DIR__ . '/src/Drivers/TwilioDriver.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
