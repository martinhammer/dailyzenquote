<?php

declare(strict_types=1);

namespace OCA\DailyZenQuote\Controller;

use OCA\DailyZenQuote\Service\QuoteFetchException;
use OCA\DailyZenQuote\Service\QuoteService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

/**
 * @psalm-suppress UnusedClass
 */
class ApiController extends OCSController {
	public function __construct(
		string $appName,
		IRequest $request,
		private QuoteService $quoteService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Get today's inspirational quote
	 *
	 * @return DataResponse<Http::STATUS_OK, array{quote: string, author: string}, array{}>|DataResponse<Http::STATUS_INTERNAL_SERVER_ERROR, array{message: string}, array{}>
	 *
	 * 200: Quote returned successfully
	 * 500: Failed to fetch quote from upstream API
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/quote')]
	public function quote(): DataResponse {
		try {
			return new DataResponse($this->quoteService->fetchQuote());
		} catch (QuoteFetchException $e) {
			return new DataResponse(
				['message' => 'Failed to fetch quote'],
				Http::STATUS_INTERNAL_SERVER_ERROR,
			);
		}
	}
}
