<?php

namespace App\Action;

use App\Domain\Preferences\Service\PreferencesService;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class Preferences
{
	private $preferencesService;

	public function __construct(PreferencesService $preferencesService)
	{
		$this->preferencesService = $preferencesService;
	}

	public function getPreferences(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->getPreferences($request->getQueryParams());

		return $response->withJson($rslt)->withStatus(200);
	}

	public function addUpdatePreferences(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->addUpdatePreferences($request->getParsedBody());

		return $response->withJson($rslt)->withStatus(200);
	}

	public function getPreferenceData(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->getPreferenceData($request->getQueryParams());

		return $response->withJson($rslt)->withStatus(200);
	}

	public function getBookingPreferences(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->getBookingPreferences($request->getQueryParams());

		return $response->withJson($rslt)->withStatus(200);
	}

	public function getMatchedStaff(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->getBookingPreferences($request->getQueryParams());

		return $response->withJson($rslt)->withStatus(200);
	}

	public function getVisitTypePreferences(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->getVisitTypePreferences($request->getQueryParams());

		return $response->withJson($rslt)->withStatus(200);
	}

	public function getItem(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->getItem($request->getQueryParams());

		return $response->withJson($rslt)->withStatus(200);
	}

	public function getItemsByType(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->getItemsByType($request->getQueryParams());

		return $response->withJson($rslt)->withStatus(200);
	}

	public function addUpdatePreferenceVisitAssign(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->addUpdatePreferenceVisitAssign($request->getParsedBody());

		return $response->withJson($rslt)->withStatus(200);
	}

	public function removeStaffPreferences(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->removeStaffPreferences($request->getParsedBody());

		return $response->withStatus(200);
	}

	public function removePreferenceVisitAssign(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->preferencesService->removePreferenceVisitAssign($request->getParsedBody());

		return $response->withStatus(200);
	}
}
