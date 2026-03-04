<?php

declare(strict_types=1);

namespace OCA\NcLanguageSwitcher\AppInfo;

use OCA\NcLanguageSwitcher\Service\LanguageService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\IUserSession;
use OCP\Util;

class Application extends App implements IBootstrap {
	public const APP_ID = 'nc_language_switcher';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		// Cookie → Accept-Language override for anonymous users
		// This runs before L10N factory reads the header
		if (isset($_COOKIE['nc_language'])) {
			$lang = preg_replace('/[^a-zA-Z_-]/', '', $_COOKIE['nc_language']);
			if ($lang !== '') {
				$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $lang;
			}
		}
	}

	public function boot(IBootContext $context): void {
		/** @var IEventDispatcher $dispatcher */
		$dispatcher = $context->getServerContainer()->get(IEventDispatcher::class);

		$dispatcher->addListener(BeforeTemplateRenderedEvent::class, function (BeforeTemplateRenderedEvent $event) use ($context) {
			$container = $context->getServerContainer();

			/** @var IConfig $config */
			$config = $container->get(IConfig::class);

			// Check if switcher is enabled by admin
			$enabled = $config->getAppValue(self::APP_ID, 'enabled', 'yes') === 'yes';
			if (!$enabled) {
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

			$initialState->provideInitialState(self::APP_ID, 'languages', $languages);
			$initialState->provideInitialState(self::APP_ID, 'currentLanguage', $currentLanguage);
			$initialState->provideInitialState(self::APP_ID, 'isLoggedIn', $isLoggedIn);

			Util::addScript(self::APP_ID, 'nc-language-switcher-main');
		});
	}
}
