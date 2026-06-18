<?php

declare(strict_types=1);

namespace Controller;

use OCA\DailyZenQuote\AppInfo\Application;
use OCA\DailyZenQuote\Controller\ApiController;
use OCA\DailyZenQuote\Service\OnThisDayService;
use OCA\DailyZenQuote\Service\QuoteFetchException;
use OCA\DailyZenQuote\Service\QuoteService;
use OCP\AppFramework\Http;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase {
	private QuoteService $quoteService;
	private OnThisDayService $onThisDayService;
	private ApiController $controller;

	protected function setUp(): void {
		$this->quoteService = $this->createMock(QuoteService::class);
		$this->onThisDayService = $this->createMock(OnThisDayService::class);
		$this->controller = new ApiController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			$this->quoteService,
			$this->onThisDayService,
		);
	}

	public function testQuoteSuccess(): void {
		$this->quoteService
			->method('fetchQuote')
			->willReturn(['quote' => 'Be present.', 'author' => 'Eckhart Tolle']);

		$response = $this->controller->quote();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame('Be present.', $response->getData()['quote']);
		$this->assertSame('Eckhart Tolle', $response->getData()['author']);
	}

	public function testQuoteUpstreamFailure(): void {
		$this->quoteService
			->method('fetchQuote')
			->willThrowException(new QuoteFetchException('Network error'));

		$response = $this->controller->quote();

		$this->assertSame(Http::STATUS_INTERNAL_SERVER_ERROR, $response->getStatus());
		$this->assertArrayHasKey('message', $response->getData());
	}

	public function testOnThisDaySuccess(): void {
		$payload = [
			'events' => [['text' => 'Something happened.']],
			'births' => [['text' => 'Someone was born.']],
			'deaths' => [],
		];
		$this->onThisDayService
			->method('fetchOnThisDay')
			->willReturn($payload);

		$response = $this->controller->onThisDay();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame($payload, $response->getData());
	}

	public function testOnThisDayUpstreamFailure(): void {
		$this->onThisDayService
			->method('fetchOnThisDay')
			->willThrowException(new QuoteFetchException('Network error'));

		$response = $this->controller->onThisDay();

		$this->assertSame(Http::STATUS_INTERNAL_SERVER_ERROR, $response->getStatus());
		$this->assertArrayHasKey('message', $response->getData());
	}
}
