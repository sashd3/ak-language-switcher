<?php
/**
 * SPDX-FileCopyrightText: 2026 aarekraft.dev - Sash Wegmüller
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\AkLanguageSwitcher\Service;

use OCP\IConfig;
use OCP\IUserSession;
use OCP\L10N\IFactory;

class LanguageService {
	private IFactory $l10nFactory;
	private IConfig $config;
	private IUserSession $userSession;

	public function __construct(
		IFactory $l10nFactory,
		IConfig $config,
		IUserSession $userSession
	) {
		$this->l10nFactory = $l10nFactory;
		$this->config = $config;
		$this->userSession = $userSession;
	}

	/**
	 * Get available languages with native display names.
	 *
	 * @return array<array{code: string, name: string}>
	 */
	public function getAvailableLanguages(): array {
		$codes = $this->l10nFactory->findAvailableLanguages('core');
		$languages = [];

		foreach ($codes as $code) {
			$name = \Locale::getDisplayName($code, $code);
			// Locale::getDisplayName returns the code itself if it can't resolve
			if ($name === $code || $name === '') {
				$name = $code;
			}
			$languages[] = [
				'code' => $code,
				'name' => $name,
			];
		}

		// Sort by display name
		usort($languages, function (array $a, array $b): int {
			return strcasecmp($a['name'], $b['name']);
		});

		return $languages;
	}

	/**
	 * Get current language for the active user or request.
	 */
	public function getCurrentLanguage(): string {
		return $this->l10nFactory->findLanguage('core');
	}

	/**
	 * Set language for authenticated user.
	 */
	public function setUserLanguage(string $lang): void {
		$user = $this->userSession->getUser();
		if ($user === null) {
			throw new \RuntimeException('No authenticated user');
		}
		$this->config->setUserValue($user->getUID(), 'core', 'lang', $lang);
	}

	/**
	 * Validate that a language code is available.
	 */
	public function isValidLanguage(string $lang): bool {
		$codes = $this->l10nFactory->findAvailableLanguages('core');
		return in_array($lang, $codes, true);
	}
}
