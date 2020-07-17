<?php

namespace App\Libraries;

use PDO;
use Psr\Container\ContainerInterface;

/**
 * A base repository to be used by all repositories.
 * Ensures the container and database objects are accessable
 */
class BaseRepository
{
	/**
	 * @var ContainerInterface $container The container
	 */
	protected $container;

	/**
	 * @var PDO The readonly database
	 */
	protected $readonly_db;

	/**
	 * @var PDO The master database
	 */
	protected $db;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->readonly_db = $this->container->get('db_readonly');
		$this->db = $this->container->get('db');

	}
}
