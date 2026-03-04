<?php

declare(strict_types=1);

namespace OCA\AkLanguageSwitcher\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
	private IL10N $l;
	private IURLGenerator $urlGenerator;

	public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
	}

	public function getID(): string {
		return 'ak_language_switcher';
	}

	public function getName(): string {
		return $this->l->t('Language Switcher');
	}

	public function getPriority(): int {
		return 50;
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath('ak_language_switcher', 'app.svg');
	}
}
