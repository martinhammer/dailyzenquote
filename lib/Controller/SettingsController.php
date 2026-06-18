<?php

declare(strict_types=1);

namespace OCA\DailyZenQuote\Controller;

use OCA\DailyZenQuote\AppInfo\Application;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * @psalm-suppress UnusedClass
 */
class SettingsController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		private IConfig $config,
		private IUserSession $userSession,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Persist the current user's widget content mode
	 *
	 * @param string $mode One of: quote, events, births_deaths, all
	 * @return DataResponse<Http::STATUS_OK, array{mode: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{message: string}, array{}>
	 *
	 * 200: Mode saved successfully
	 * 400: Invalid mode supplied
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/settings/mode')]
	public function setMode(string $mode): DataResponse {
		if (!in_array($mode, Application::MODES, true)) {
			return new DataResponse(
				['message' => 'Invalid mode'],
				Http::STATUS_BAD_REQUEST,
			);
		}

		$user = $this->userSession->getUser();
		if ($user === null) {
			return new DataResponse(
				['message' => 'No user session'],
				Http::STATUS_BAD_REQUEST,
			);
		}

		$this->config->setUserValue($user->getUID(), Application::APP_ID, 'mode', $mode);

		return new DataResponse(['mode' => $mode]);
	}
}
