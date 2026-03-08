<?php
/**
 * SPDX-FileCopyrightText: 2026 aarekraft.dev - Sash Wegmüller
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

return [
	'routes' => [
		['name' => 'language#getLanguages', 'url' => '/languages', 'verb' => 'GET'],
		['name' => 'language#setLanguage', 'url' => '/language', 'verb' => 'POST'],
		['name' => 'admin#saveSettings', 'url' => '/admin/settings', 'verb' => 'POST'],
	],
];
