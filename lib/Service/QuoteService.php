<?php

declare(strict_types=1);

namespace OCA\DailyZenQuote\Service;

use OCP\Http\Client\IClientService;
use OCP\ICacheFactory;

class QuoteService {
	private const ZENQUOTES_URL = 'https://zenquotes.io/api/today';
	private const CACHE_TTL = 86400;

	public function __construct(
		private IClientService $clientService,
		private ICacheFactory $cacheFactory,
	) {
	}

	/**
	 * Fetch today's quote, using a distributed cache to avoid redundant upstream calls.
	 *
	 * @return array{quote: string, author: string}
	 * @throws QuoteFetchException
	 */
	public function fetchQuote(): array {
		$cache = $this->cacheFactory->createDistributed('dailyzenquote');
		$cacheKey = 'quote_' . date('Y-m-d');

		/** @var array{quote: string, author: string}|null $cached */
		$cached = $cache->get($cacheKey);
		if ($cached !== null) {
			return $cached;
		}

		try {
			$client = $this->clientService->newClient();
			$response = $client->get(self::ZENQUOTES_URL);
			$data = json_decode($response->getBody(), true);
		} catch (\Exception $e) {
			throw new QuoteFetchException('Failed to fetch quote from ZenQuotes API: ' . $e->getMessage(), 0, $e);
		}

		if (!is_array($data) || empty($data) || !isset($data[0]['q'], $data[0]['a'])) {
			throw new QuoteFetchException('Unexpected response format from ZenQuotes API');
		}

		$result = [
			'quote' => (string) $data[0]['q'],
			'author' => (string) $data[0]['a'],
		];

		$cache->set($cacheKey, $result, self::CACHE_TTL);

		return $result;
	}
}
