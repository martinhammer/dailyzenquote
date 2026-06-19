<?php

declare(strict_types=1);

namespace OCA\DailyZenQuote\Dashboard;

use OCA\DailyZenQuote\AppInfo\Application;
use OCP\AppFramework\Services\IInitialState;
use OCP\Config\IUserConfig;
use OCP\Dashboard\IWidget;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Util;

/**
 * @psalm-api
 * @psalm-suppress UnusedClass
 */
class ZenQuoteWidget implements IWidget {
	public function __construct(
		private IURLGenerator $urlGenerator,
		private IUserConfig $config,
		private IUserSession $userSession,
		private IInitialState $initialState,
	) {
	}

	public function getId(): string {
		return Application::APP_ID;
	}

	public function getTitle(): string {
		return $this->getMode() === Application::MODE_QUOTE
			? 'Daily Zen Quote'
			: 'On This Day';
	}

	public function getOrder(): int {
		return 50;
	}

	public function getIconClass(): string {
		return 'icon-dailyzenquote';
	}

	public function getIconUrl(): string {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
	}

	public function getUrl(): ?string {
		return null;
	}

	public function load(): void {
		$this->initialState->provideInitialState('mode', $this->getMode());

		Util::addScript(Application::APP_ID, Application::APP_ID . '-widget');
		Util::addStyle(Application::APP_ID, Application::APP_ID . '-widget');
	}

	/**
	 * The current user's selected widget content mode, falling back to the
	 * default for anonymous sessions or unrecognised stored values.
	 */
	private function getMode(): string {
		$user = $this->userSession->getUser();
		if ($user === null) {
			return Application::DEFAULT_MODE;
		}

		$mode = $this->config->getValueString($user->getUID(), Application::APP_ID, 'mode', Application::DEFAULT_MODE);

		return in_array($mode, Application::MODES, true) ? $mode : Application::DEFAULT_MODE;
	}
}
