<?php
/**
 * SPDX-FileCopyrightText: 2026 aarekraft.dev - Sash Wegmüller
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

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

	/** Accept-Language header as it arrived, before register() overrode it. */
	private static ?string $originalAcceptLanguage = null;

	/** Whether register() actually replaced the Accept-Language header. */
	private static bool $acceptLanguageOverridden = false;

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

	/**
	 * Expire the nc_language cookie and stop it affecting the current request.
	 */
	private static function clearCookie(): void {
		unset($_COOKIE['nc_language']);
		if (!headers_sent()) {
			setcookie('nc_language', '', ['expires' => 1, 'path' => '/', 'samesite' => 'Lax']);
		}
	}

	/**
	 * Undo the Accept-Language override applied by register().
	 */
	private static function restoreAcceptLanguage(): void {
		if (!self::$acceptLanguageOverridden) {
			return;
		}
		if (self::$originalAcceptLanguage === null) {
			unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		} else {
			$_SERVER['HTTP_ACCEPT_LANGUAGE'] = self::$originalAcceptLanguage;
		}
		self::$acceptLanguageOverridden = false;
	}

	/**
	 * Whether a user is signed in. Fails closed to "anonymous" so that a
	 * container/session hiccup keeps the previous (public-page) behaviour.
	 */
	private static function isLoggedIn(IBootContext $context): bool {
		try {
			return $context->getServerContainer()->get(IUserSession::class)->isLoggedIn();
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function register(IRegistrationContext $context): void {
		// Cookie → Accept-Language override for anonymous users.
		// This runs too early to know whether anyone is signed in, so boot()
		// re-checks and reverts this for logged-in users.
		$lang = self::getCookieLanguage();
		if ($lang !== null) {
			self::$originalAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
			self::$acceptLanguageOverridden = true;
			$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $lang;
		}
	}

	public function boot(IBootContext $context): void {
		// If cookie is set, force-reset the L10N Factory cache via reflection.
		// This handles the case where another app triggered findLanguage()
		// before our register() ran, caching the browser language.
		//
		// The cookie is only meant for anonymous visitors on public pages.
		// Logged-in users have a real language preference (core/lang), which
		// the L10N Factory resolves *after* requestLanguage — so a leftover
		// cookie from an earlier public-share visit would silently override
		// their saved language. Skip the override and drop the stale cookie.
		$lang = self::getCookieLanguage();
		if ($lang !== null && self::isLoggedIn($context)) {
			self::clearCookie();
			self::restoreAcceptLanguage();
			$lang = null;
		}
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
					self::clearCookie();
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
			$capitalizeNames = $config->getAppValue(self::APP_ID, 'capitalize_names', 'yes') === 'yes';

			$initialState->provideInitialState(self::APP_ID, 'languages', $languages);
			$initialState->provideInitialState(self::APP_ID, 'currentLanguage', $currentLanguage);
			$initialState->provideInitialState(self::APP_ID, 'isLoggedIn', $isLoggedIn);
			$initialState->provideInitialState(self::APP_ID, 'icon', $icon);
			$initialState->provideInitialState(self::APP_ID, 'iconSize', $iconSize);
			$initialState->provideInitialState(self::APP_ID, 'iconColor', $iconColor);
			$initialState->provideInitialState(self::APP_ID, 'iconStrokeWidth', (float) $iconStrokeWidth);
			$initialState->provideInitialState(self::APP_ID, 'capitalizeNames', $capitalizeNames);

			Util::addScript(self::APP_ID, 'ak-language-switcher-main');
		});
	}
}
