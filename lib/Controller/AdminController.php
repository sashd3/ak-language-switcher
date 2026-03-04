<?php

declare(strict_types=1);

namespace OCA\AkLanguageSwitcher\Controller;

use OCA\AkLanguageSwitcher\AppInfo\Application;
use OCA\AkLanguageSwitcher\Service\LanguageService;
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
	public function saveSettings(
		bool $enabled,
		array $allowedLanguages,
		string $icon = 'Globe',
		int $iconSize = 20,
		string $iconColor = '',
		float $iconStrokeWidth = 2
	): JSONResponse {
		$this->config->setAppValue(Application::APP_ID, 'enabled', $enabled ? 'yes' : 'no');

		// Validate language codes
		$validCodes = array_column($this->languageService->getAvailableLanguages(), 'code');
		$filtered = array_values(array_filter($allowedLanguages, fn($code) => in_array($code, $validCodes, true)));
		$this->config->setAppValue(Application::APP_ID, 'allowed_languages', implode(',', $filtered));

		// Icon settings
		$allowedIcons = ['Globe', 'Languages', 'Earth', 'MessageCircle', 'LetterType', 'BookOpen'];
		if (in_array($icon, $allowedIcons, true)) {
			$this->config->setAppValue(Application::APP_ID, 'icon', $icon);
		}

		$iconSize = max(14, min(32, $iconSize));
		$this->config->setAppValue(Application::APP_ID, 'icon_size', (string) $iconSize);

		// Validate color: hex format or empty
		if ($iconColor === '' || preg_match('/^#[0-9a-fA-F]{6}$/', $iconColor)) {
			$this->config->setAppValue(Application::APP_ID, 'icon_color', $iconColor);
		}

		// Stroke width: 0.5 to 4
		$iconStrokeWidth = max(0.5, min(4, $iconStrokeWidth));
		$this->config->setAppValue(Application::APP_ID, 'icon_stroke_width', (string) $iconStrokeWidth);

		return new JSONResponse(['status' => 'ok']);
	}
}
