<?php

declare(strict_types=1);

namespace OCA\NcLanguageSwitcher\Controller;

use OCA\NcLanguageSwitcher\AppInfo\Application;
use OCA\NcLanguageSwitcher\Service\LanguageService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class LanguageController extends Controller {
	private LanguageService $languageService;

	public function __construct(
		IRequest $request,
		LanguageService $languageService
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->languageService = $languageService;
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
		if (!$this->languageService->isValidLanguage($lang)) {
			return new JSONResponse(
				['error' => 'Invalid language code'],
				Http::STATUS_BAD_REQUEST
			);
		}

		$this->languageService->setUserLanguage($lang);

		return new JSONResponse(['status' => 'ok', 'lang' => $lang]);
	}
}
