<?php

declare(strict_types=1);

namespace OCA\AkLanguageSwitcher\Settings;

use OCA\AkLanguageSwitcher\AppInfo\Application;
use OCA\AkLanguageSwitcher\Service\LanguageService;
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

		$icon = $this->config->getAppValue(Application::APP_ID, 'icon', 'Globe');
		$iconSize = (int) $this->config->getAppValue(Application::APP_ID, 'icon_size', '20');
		$iconColor = $this->config->getAppValue(Application::APP_ID, 'icon_color', '');
		$iconStrokeWidth = $this->config->getAppValue(Application::APP_ID, 'icon_stroke_width', '2');

		$this->initialState->provideInitialState(Application::APP_ID, 'adminEnabled', $enabled);
		$this->initialState->provideInitialState(Application::APP_ID, 'adminAllowedLanguages', $allowedList);
		$this->initialState->provideInitialState(Application::APP_ID, 'adminAllLanguages', $this->languageService->getAvailableLanguages());
		$this->initialState->provideInitialState(Application::APP_ID, 'adminIcon', $icon);
		$this->initialState->provideInitialState(Application::APP_ID, 'adminIconSize', $iconSize);
		$this->initialState->provideInitialState(Application::APP_ID, 'adminIconColor', $iconColor);
		$this->initialState->provideInitialState(Application::APP_ID, 'adminIconStrokeWidth', (float) $iconStrokeWidth);

		Util::addScript(Application::APP_ID, 'ak-language-switcher-admin');

		return new TemplateResponse(Application::APP_ID, 'settings/admin');
	}

	public function getSection(): string {
		return 'ak_language_switcher';
	}

	public function getPriority(): int {
		return 50;
	}
}
