<?php

declare(strict_types=1);

namespace Service;

use OCA\DailyZenQuote\Service\OnThisDayService;
use OCA\DailyZenQuote\Service\QuoteFetchException;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\ICache;
use OCP\ICacheFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class OnThisDayServiceTest extends TestCase {
	private IClientService&MockObject $clientService;
	private ICacheFactory&MockObject $cacheFactory;
	private ICache&MockObject $cache;
	private OnThisDayService $service;

	protected function setUp(): void {
		$this->clientService = $this->createMock(IClientService::class);
		$this->cacheFactory = $this->createMock(ICacheFactory::class);
		$this->cache = $this->createMock(ICache::class);
		$this->cacheFactory->method('createDistributed')->willReturn($this->cache);
		$this->service = new OnThisDayService($this->clientService, $this->cacheFactory);
	}

	public function testReturnsCachedValueWithoutUpstreamCall(): void {
		$cached = ['events' => [['text' => 'cached']], 'births' => [], 'deaths' => []];
		$this->cache->method('get')->willReturn($cached);
		$this->clientService->expects($this->never())->method('newClient');

		$this->assertSame($cached, $this->service->fetchOnThisDay());
	}

	public function testFetchesParsesAndCaches(): void {
		$this->cache->method('get')->willReturn(null);

		$body = json_encode([
			'data' => [
				'Events' => [['text' => 'An <a href="x">event</a> happened']],
				'Births' => [['text' => 'Someone born'], ['not_text' => 'skip me']],
				'Deaths' => [['text' => '  ']],
			],
		]);

		$response = $this->createMock(IResponse::class);
		$response->method('getBody')->willReturn($body);

		$expectedUrl = 'https://today.zenquotes.io/api/' . (int)date('n') . '/' . (int)date('j');
		$client = $this->createMock(IClient::class);
		$client->expects($this->once())
			->method('get')
			->with($expectedUrl)
			->willReturn($response);
		$this->clientService->method('newClient')->willReturn($client);

		$this->cache->expects($this->once())->method('set');

		$result = $this->service->fetchOnThisDay();

		// HTML stripped, malformed/blank entries dropped.
		$this->assertSame([['text' => 'An event happened']], $result['events']);
		$this->assertSame([['text' => 'Someone born']], $result['births']);
		$this->assertSame([], $result['deaths']);
	}

	public function testMissingSectionsYieldEmptyLists(): void {
		$this->cache->method('get')->willReturn(null);

		$response = $this->createMock(IResponse::class);
		$response->method('getBody')->willReturn(json_encode(['data' => []]));
		$client = $this->createMock(IClient::class);
		$client->method('get')->willReturn($response);
		$this->clientService->method('newClient')->willReturn($client);

		$result = $this->service->fetchOnThisDay();

		$this->assertSame(['events' => [], 'births' => [], 'deaths' => []], $result);
	}

	public function testThrowsOnUnexpectedPayload(): void {
		$this->cache->method('get')->willReturn(null);

		$response = $this->createMock(IResponse::class);
		$response->method('getBody')->willReturn(json_encode(['unexpected' => true]));
		$client = $this->createMock(IClient::class);
		$client->method('get')->willReturn($response);
		$this->clientService->method('newClient')->willReturn($client);

		$this->expectException(QuoteFetchException::class);
		$this->service->fetchOnThisDay();
	}

	public function testThrowsOnHttpError(): void {
		$this->cache->method('get')->willReturn(null);

		$client = $this->createMock(IClient::class);
		$client->method('get')->willThrowException(new \RuntimeException('boom'));
		$this->clientService->method('newClient')->willReturn($client);

		$this->expectException(QuoteFetchException::class);
		$this->service->fetchOnThisDay();
	}
}
