<?php

declare(strict_types=1);

namespace OCA\DailyZenQuote\AppInfo;

use OCA\DailyZenQuote\Dashboard\ZenQuoteWidget;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'dailyzenquote';

	/** Widget content modes, persisted as the per-user 'mode' preference. */
	public const MODE_QUOTE = 'quote';
	public const MODE_EVENTS = 'events';
	public const MODE_BIRTHS_DEATHS = 'births_deaths';
	public const MODE_ALL = 'all';

	public const DEFAULT_MODE = self::MODE_QUOTE;

	public const MODES = [
		self::MODE_QUOTE,
		self::MODE_EVENTS,
		self::MODE_BIRTHS_DEATHS,
		self::MODE_ALL,
	];

	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerDashboardWidget(ZenQuoteWidget::class);
	}

	public function boot(IBootContext $context): void {
	}
}
