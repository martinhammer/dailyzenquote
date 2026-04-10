<?php

declare(strict_types=1);

namespace OCA\DailyZenQuote\Dashboard;

use OCA\DailyZenQuote\AppInfo\Application;
use OCP\Dashboard\IWidget;
use OCP\IURLGenerator;
use OCP\Util;

/**
 * @psalm-suppress UnusedClass
 */
class ZenQuoteWidget implements IWidget {
	public function __construct(
		private IURLGenerator $urlGenerator,
	) {
	}

	public function getId(): string {
		return Application::APP_ID;
	}

	public function getTitle(): string {
		return 'Zen Quote of the Day';
	}

	public function getOrder(): int {
		return 50;
	}

	public function getIconClass(): string {
		return 'icon-dailyzenquote';
	}

	public function getIconUrl(): string {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app.svg');
	}

	public function getUrl(): string {
		return $this->urlGenerator->linkToRouteAbsolute('dailyzenquote.page.index');
	}

	public function load(): void {
		Util::addScript(Application::APP_ID, Application::APP_ID . '-widget');
		Util::addStyle(Application::APP_ID, Application::APP_ID . '-widget');
	}
}
