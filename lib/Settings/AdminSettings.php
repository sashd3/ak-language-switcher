<?php

declare(strict_types=1);

namespace OCA\NcLanguageSwitcher\Settings;

use OCA\NcLanguageSwitcher\AppInfo\Application;
use OCA\NcLanguageSwitcher\Service\LanguageService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\Settings\ISettings;
use OCP\Util;

class AdminSettings implements ISettings {
	private IConfig $config;
	private IInitialStateService $initialState;
	private LanguageService $languageService;

	public function __construct(
		IConfig $config,
		IInitialStateService $initialState,
		LanguageService $languageService
	) {
		$this->config = $config;
		$this->initialState = $initialState;
		$this->languageService = $languageService;
	}

	public function getForm(): TemplateResponse {
		$enabled = $this->config->getAppValue(Application::APP_ID, 'enabled', 'yes') === 'yes';
		$allowedLanguages = $this->config->getAppValue(Application::APP_ID, 'allowed_languages', '');
		$allowedList = $allowedLanguages !== '' ? explode(',', $allowedLanguages) : [];

		$this->initialState->provideInitialState(Application::APP_ID, 'adminEnabled', $enabled);
		$this->initialState->provideInitialState(Application::APP_ID, 'adminAllowedLanguages', $allowedList);
		$this->initialState->provideInitialState(Application::APP_ID, 'adminAllLanguages', $this->languageService->getAvailableLanguages());

		Util::addScript(Application::APP_ID, 'nc-language-switcher-admin');

		return new TemplateResponse(Application::APP_ID, 'settings/admin');
	}

	public function getSection(): string {
		return 'additional';
	}

	public function getPriority(): int {
		return 50;
	}
}
