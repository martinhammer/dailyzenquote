<?php

declare(strict_types=1);

use OCA\DailyZenQuote\AppInfo\Application;
use OCP\Util;

Util::addScript(Application::APP_ID, Application::APP_ID . '-settings');
Util::addStyle(Application::APP_ID, Application::APP_ID . '-settings');
?>

<div id="dailyzenquote-settings"></div>
