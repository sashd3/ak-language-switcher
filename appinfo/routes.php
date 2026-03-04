<?php

declare(strict_types=1);

return [
	'routes' => [
		['name' => 'language#getLanguages', 'url' => '/languages', 'verb' => 'GET'],
		['name' => 'language#setLanguage', 'url' => '/language', 'verb' => 'POST'],
		['name' => 'admin#saveSettings', 'url' => '/admin/settings', 'verb' => 'POST'],
	],
];
