<?php

declare(strict_types=1);

namespace OCA\NcLanguageSwitcher\Controller;

use OCA\NcLanguageSwitcher\AppInfo\Application;
use OCA\NcLanguageSwitcher\Service\LanguageService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;

class AdminController extends Controller {
	private IConfig $config;
	private LanguageService $languageService;

	public function __construct(
		IRequest $request,
		IConfig $config,
		LanguageService $languageService
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->config = $config;
		$this->languageService = $languageService;
	}

	/**
	 * Save admin settings. Only admins can call this (no #[NoAdminRequired]).
	 */
	public function saveSettings(bool $enabled, array $allowedLanguages): JSONResponse {
		$this->config->setAppValue(Application::APP_ID, 'enabled', $enabled ? 'yes' : 'no');

		// Validate language codes
		$validCodes = array_column($this->languageService->getAvailableLanguages(), 'code');
		$filtered = array_values(array_filter($allowedLanguages, fn($code) => in_array($code, $validCodes, true)));

		$this->config->setAppValue(Application::APP_ID, 'allowed_languages', implode(',', $filtered));

		return new JSONResponse(['status' => 'ok']);
	}
}
