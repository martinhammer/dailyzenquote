<?php

declare(strict_types=1);

namespace OCA\DailyZenQuote\Settings;

use OCA\DailyZenQuote\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\Settings\ISettings;

/**
 * @psalm-suppress UnusedClass
 */
class PersonalSettings implements ISettings {
	public function __construct(
		private IInitialState $initialState,
		private IConfig $config,
		private IUserSession $userSession,
	) {
	}

	public function getForm(): TemplateResponse {
		$user = $this->userSession->getUser();
		$mode = $user !== null
			? $this->config->getUserValue($user->getUID(), Application::APP_ID, 'mode', Application::DEFAULT_MODE)
			: Application::DEFAULT_MODE;

		$this->initialState->provideInitialState('mode', $mode);

		return new TemplateResponse(Application::APP_ID, 'settings');
	}

	public function getSection(): string {
		return Application::APP_ID;
	}

	public function getPriority(): int {
		return 50;
	}
}
