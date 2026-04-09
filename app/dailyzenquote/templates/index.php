<?php

declare(strict_types=1);

use OCP\Util;

Util::addScript(OCA\DailyZenQuote\AppInfo\Application::APP_ID, OCA\DailyZenQuote\AppInfo\Application::APP_ID . '-main');
Util::addStyle(OCA\DailyZenQuote\AppInfo\Application::APP_ID, OCA\DailyZenQuote\AppInfo\Application::APP_ID . '-main');

?>

<div id="dailyzenquote"></div>
