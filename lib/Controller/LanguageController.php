<?php
/**
 * SPDX-FileCopyrightText: 2026 aarekraft.dev - Sash Wegmüller
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\AkLanguageSwitcher\Controller;

use OCA\AkLanguageSwitcher\AppInfo\Application;
use OCA\AkLanguageSwitcher\Service\LanguageService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;

class LanguageController extends Controller {
	private LanguageService $languageService;
	private IConfig $config;

	public function __construct(
		IRequest $request,
		LanguageService $languageService,
		IConfig $config
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->languageService = $languageService;
		$this->config = $config;
	}

	/**
	 * Get available languages.
	 */
	#[PublicPage]
	#[NoCSRFRequired]
	public function getLanguages(): JSONResponse {
		return new JSONResponse($this->languageService->getAvailableLanguages());
	}

	/**
	 * Set language for authenticated user.
	 */
	#[NoAdminRequired]
	public function setLanguage(string $lang): JSONResponse {
		// Check if switcher is enabled
		if ($this->config->getAppValue(Application::APP_ID, 'enabled', 'yes') !== 'yes') {
			return new JSONResponse(
				['error' => 'Language switcher is disabled'],
				Http::STATUS_FORBIDDEN
			);
		}

		if (!$this->languageService->isValidLanguage($lang)) {
			return new JSONResponse(
				['error' => 'Invalid language code'],
				Http::STATUS_BAD_REQUEST
			);
		}

		// Check against admin-restricted language list
		$allowedStr = $this->config->getAppValue(Application::APP_ID, 'allowed_languages', '');
		if ($allowedStr !== '') {
			$allowedCodes = explode(',', $allowedStr);
			if (!in_array($lang, $allowedCodes, true)) {
				return new JSONResponse(
					['error' => 'Language not allowed'],
					Http::STATUS_FORBIDDEN
				);
			}
		}

		$this->languageService->setUserLanguage($lang);

		return new JSONResponse(['status' => 'ok', 'lang' => $lang]);
	}
}
