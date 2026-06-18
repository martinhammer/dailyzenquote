<?php

declare(strict_types=1);

namespace OCA\DailyZenQuote\Service;

use OCP\Http\Client\IClientService;
use OCP\ICacheFactory;

/**
 * @psalm-api
 *
 * @psalm-type OnThisDayEntry = array{text: string}
 */
class OnThisDayService {
	private const ZENQUOTES_URL = 'https://today.zenquotes.io/api';
	private const CACHE_TTL = 86400;

	public function __construct(
		private IClientService $clientService,
		private ICacheFactory $cacheFactory,
	) {
	}

	/**
	 * Fetch today's "On This Day" historical entries, using a distributed cache
	 * to avoid redundant upstream calls.
	 *
	 * @return array{events: list<OnThisDayEntry>, births: list<OnThisDayEntry>, deaths: list<OnThisDayEntry>}
	 * @throws QuoteFetchException
	 */
	public function fetchOnThisDay(): array {
		$cache = $this->cacheFactory->createDistributed('dailyzenquote');
		$cacheKey = 'onthisday_' . date('Y-m-d');

		/** @var array{events: list<OnThisDayEntry>, births: list<OnThisDayEntry>, deaths: list<OnThisDayEntry>}|null $cached */
		$cached = $cache->get($cacheKey);
		if ($cached !== null) {
			return $cached;
		}

		// Unpadded month/day, e.g. .../api/6/18
		$url = self::ZENQUOTES_URL . '/' . (int)date('n') . '/' . (int)date('j');

		try {
			$client = $this->clientService->newClient();
			$response = $client->get($url);
			$data = json_decode((string)$response->getBody(), true);
		} catch (\Exception $e) {
			throw new QuoteFetchException('Failed to fetch On This Day data from ZenQuotes API: ' . $e->getMessage(), 0, $e);
		}

		if (!is_array($data) || !isset($data['data']) || !is_array($data['data'])) {
			throw new QuoteFetchException('Unexpected response format from ZenQuotes On This Day API');
		}

		$result = [
			'events' => $this->parseSection($data['data'], 'Events'),
			'births' => $this->parseSection($data['data'], 'Births'),
			'deaths' => $this->parseSection($data['data'], 'Deaths'),
		];

		$cache->set($cacheKey, $result, self::CACHE_TTL);

		return $result;
	}

	/**
	 * Extract a section's entries as a list of plain-text items.
	 * Missing or malformed sections yield an empty list; HTML is stripped defensively.
	 *
	 * @param array<array-key, mixed> $sections
	 * @return list<OnThisDayEntry>
	 */
	private function parseSection(array $sections, string $key): array {
		if (!isset($sections[$key]) || !is_array($sections[$key])) {
			return [];
		}

		$entries = [];
		foreach ($sections[$key] as $entry) {
			if (!is_array($entry) || !isset($entry['text'])) {
				continue;
			}
			$text = trim(strip_tags((string)$entry['text']));
			if ($text !== '') {
				$entries[] = ['text' => $text];
			}
		}

		return $entries;
	}
}
