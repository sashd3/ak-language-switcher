<?php

declare(strict_types=1);

namespace OCA\AkLanguageSwitcher\AppInfo;

use OCA\AkLanguageSwitcher\Service\LanguageService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use OCP\Util;

class Application extends App implements IBootstrap {
	public const APP_ID = 'ak_language_switcher';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	/**
	 * Read and sanitize nc_language cookie value.
	 */
	private static function getCookieLanguage(): ?string {
		if (!isset($_COOKIE['nc_language'])) {
			return null;
		}
		$raw = $_COOKIE['nc_language'];
		if (strlen($raw) > 10) {
			return null;
		}
		$lang = preg_replace('/[^a-zA-Z_-]/', '', $raw);
		return $lang !== '' ? $lang : null;
	}

	public function register(IRegistrationContext $context): void {
		// Cookie → Accept-Language override for anonymous users
		// This runs early but the L10N Factory might already have cached its result
		$lang = self::getCookieLanguage();
		if ($lang !== null) {
			$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $lang;
		}
	}

	public function boot(IBootContext $context): void {
		// If cookie is set, force-reset the L10N Factory cache via reflection.
		// This handles the case where another app triggered findLanguage()
		// before our register() ran, caching the browser language.
		$lang = self::getCookieLanguage();
		if ($lang !== null) {
			try {
				$factory = $context->getServerContainer()->get(IFactory::class);
				$ref = new \ReflectionProperty($factory, 'requestLanguage');
				$ref->setAccessible(true);
				$ref->setValue($factory, $lang);
			} catch (\Throwable $e) {
				// Reflection may fail on different NC versions — Accept-Language override still works
			}
		}

		/** @var IEventDispatcher $dispatcher */
		$dispatcher = $context->getServerContainer()->get(IEventDispatcher::class);

		$dispatcher->addListener(BeforeTemplateRenderedEvent::class, function (BeforeTemplateRenderedEvent $event) use ($context) {
			$container = $context->getServerContainer();

			/** @var IConfig $config */
			$config = $container->get(IConfig::class);

			// Check if switcher is enabled by admin
			$enabled = $config->getAppValue(self::APP_ID, 'enabled', 'yes') === 'yes';
			if (!$enabled) {
				if (isset($_COOKIE['nc_language'])) {
					setcookie('nc_language', '', ['expires' => 1, 'path' => '/', 'samesite' => 'Lax']);
				}
				return;
			}

			/** @var LanguageService $languageService */
			$languageService = $container->get(LanguageService::class);

			/** @var IInitialStateService $initialState */
			$initialState = $container->get(IInitialStateService::class);

			/** @var IUserSession $userSession */
			$userSession = $container->get(IUserSession::class);

			$languages = $languageService->getAvailableLanguages();

			// Filter by allowed languages if admin has configured a restriction
			$allowedStr = $config->getAppValue(self::APP_ID, 'allowed_languages', '');
			if ($allowedStr !== '') {
				$allowedCodes = explode(',', $allowedStr);
				$languages = array_values(array_filter($languages, fn($l) => in_array($l['code'], $allowedCodes, true)));
			}

			$currentLanguage = $languageService->getCurrentLanguage();
			$isLoggedIn = $userSession->isLoggedIn();

			$icon = $config->getAppValue(self::APP_ID, 'icon', 'Globe');
			$iconSize = (int) $config->getAppValue(self::APP_ID, 'icon_size', '20');
			$iconColor = $config->getAppValue(self::APP_ID, 'icon_color', '');
			$iconStrokeWidth = $config->getAppValue(self::APP_ID, 'icon_stroke_width', '2');

			$initialState->provideInitialState(self::APP_ID, 'languages', $languages);
			$initialState->provideInitialState(self::APP_ID, 'currentLanguage', $currentLanguage);
			$initialState->provideInitialState(self::APP_ID, 'isLoggedIn', $isLoggedIn);
			$initialState->provideInitialState(self::APP_ID, 'icon', $icon);
			$initialState->provideInitialState(self::APP_ID, 'iconSize', $iconSize);
			$initialState->provideInitialState(self::APP_ID, 'iconColor', $iconColor);
			$initialState->provideInitialState(self::APP_ID, 'iconStrokeWidth', (float) $iconStrokeWidth);

			Util::addScript(self::APP_ID, 'ak-language-switcher-main');
		});
	}
}
