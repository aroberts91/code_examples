<?php

namespace App\Action;

use App\Domain\Events\Service\EventsService;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class Events
{
	private $eventsService;

	public function __construct(EventsService $eventsService)
	{
		$this->eventsService = $eventsService;
	}

	public function addUpdateEvent(ServerRequest $request, Response $response)
	{
		$rslt = $this->eventsService->addUpdateEvent($request->getParsedBody());

		return $response->withJson(['success' => $rslt])->withStatus(200);
	}

	public function checkEventExists(ServerRequest $request, Response $response)
	{
		$rslt = $this->eventsService->checkEventExists($request->getQueryParam('event_id'));

		return $response->withJson($rslt)->withStatus(200);
	}

	public function getEvents(ServerRequest $request, Response $response)
	{
		$args = $request->getQueryParams();

		$read = array_key_exists('read', $args) ? $args['read'] : FALSE;
		$limit = array_key_exists('limit', $args) ? $args['limit'] : NULL;
		$count = array_key_exists('count', $args) ? $args['count'] : FALSE;

		$rslt = $this->eventsService->getEvents($read, $limit, $count);

		return $response->withJson($rslt)->withStatus(200);
	}

	public function getEventsCount(ServerRequest $request, Response $response)
	{
		$args = $request->getQueryParams();

		$read = array_key_exists('read', $args) ? $args['read'] : FALSE;
		$limit = array_key_exists('limit', $args) ? $args['limit'] : NULL;

		$rslt = $this->eventsService->getEventsCount($read, $limit);

		return $response->withJson($rslt)->withStatus(200);
	}
}
