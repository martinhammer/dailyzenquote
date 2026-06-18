<?php

declare(strict_types=1);

namespace Controller;

use OCA\DailyZenQuote\AppInfo\Application;
use OCA\DailyZenQuote\Controller\SettingsController;
use OCP\AppFramework\Http;
use OCP\Config\IUserConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SettingsControllerTest extends TestCase {
	private IUserConfig&MockObject $config;
	private IUserSession&MockObject $userSession;
	private SettingsController $controller;

	protected function setUp(): void {
		$this->config = $this->createMock(IUserConfig::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->controller = new SettingsController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			$this->config,
			$this->userSession,
		);
	}

	private function mockUser(string $uid): void {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($uid);
		$this->userSession->method('getUser')->willReturn($user);
	}

	public function testValidModePersistsAndReturns(): void {
		$this->mockUser('alice');
		$this->config->expects($this->once())
			->method('setValueString')
			->with('alice', Application::APP_ID, 'mode', 'all');

		$response = $this->controller->setMode('all');

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['mode' => 'all'], $response->getData());
	}

	public function testInvalidModeRejected(): void {
		$this->config->expects($this->never())->method('setValueString');

		$response = $this->controller->setMode('bogus');

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
		$this->assertArrayHasKey('message', $response->getData());
	}

	public function testNoUserSessionRejected(): void {
		$this->userSession->method('getUser')->willReturn(null);
		$this->config->expects($this->never())->method('setValueString');

		$response = $this->controller->setMode('events');

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
	}
}
