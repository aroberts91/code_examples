<?php

namespace App\Domain\Events\Service;

use App\Domain\Events\Repository\EventsRepository;
use Psr\Container\ContainerInterface;

/**
 * Events Service (Controller)
 */
final class EventsService
{
	/**
	 * @var EventsRepository $repository
	 */
	private $repository;

	/**
	 * The constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->repository = new EventsRepository($container);
	}

	/**
	 * @param array $data The event data
	 * @return bool
	 */
	public function addUpdateEvent($data)
	{
		return $this->repository->addUpdateEvent($data);
	}

	/**
	 * @param string $event_id The id to check
	 * @return mixed
	 */
	public function checkEventExists($event_id)
	{
		return $this->repository->checkEventExists($event_id);
	}

	/**
	 * @param bool $read
	 * @param null $limit
	 * @param bool $count
	 * @return mixed
	 */
	public function getEvents($read = FALSE, $limit = NULL, $count = FALSE)
	{
		return $this->repository->getEvents($read, $limit, $count);
	}

	/**
	 * @param $read
	 * @param $length
	 * @return mixed
	 */
	public function getEventsCount($read, $length)
	{
		return $this->repository->getEventsCount($read, $length);
	}
}
