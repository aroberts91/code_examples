<?php

namespace App\Action;


use App\Domain\Settings\Service\SettingsService;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class Settings
{
	private $settingsReader;

	public function __construct(SettingsService $settingsReader)
	{
		$this->settingsReader = $settingsReader;
	}

	public function getSettings(ServerRequest $request, Response $response): Response
	{
		//Load settings data
		$rslt = $this->settingsReader->getSettings();

		//Build the HTTP response
		return $response->withJson($rslt)->withStatus(200);
	}

	public function saveSettings(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->settingsReader->saveSettings($request->getParsedBody());

		return $response->withJson(['success' => $rslt])->withStatus(200);
	}

	public function flushSettings(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->settingsReader->flushSettings();

		return $response->withJson(['success' => $rslt])->withStatus(200);
	}

	public function updateLogo(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->settingsReader->updateLogo($request->getParsedBody());

		return $response->withJson(['success' => $rslt])->withStatus(200);
	}

	public function getHeadlineStats(ServerRequest $request, Response $response): Response
	{
		$rslt = $this->settingsReader->getHeadlineStats();

		return $response->withJson([$rslt])->withStatus(200);
	}
}