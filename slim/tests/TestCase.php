<?php
declare(strict_types=1);

namespace Tests;

use DI\ContainerBuilder;
use Exception;
use App\Libraries\Database;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Psr\Container\ContainerInterface;
use Selective\Config\Configuration;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

class TestCase extends PHPUnit_TestCase
{
	/**
	 * @return App
	 * @throws Exception
	 */
	protected $db;
	private $readonly_db;

	protected function getAppInstance(): App
	{
		// Instantiate PHP-DI ContainerBuilder
		$containerBuilder = new ContainerBuilder();

		// Set up dependencies
		$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');

		// Build PHP-DI Container instance
		try {
			$container = $containerBuilder->build();
		} catch (Exception $e) {
			echo $e;
		}

		//Define the demo database for tests
		if(!defined('USER_DB')) define('USER_DB', 'careforapibeta');

		$config = $container->get(Configuration::class)->getArray('db');

		$dsn = "mysql:host=" . $config['host'] . ";dbname=" . USER_DB . ";";

		$this->db = new Database($dsn, $config['username'], $config['password'], $config['flags']);

		//Pass the database object into the container as both master and readonly to be used by repositories
		$container->set('db', $this->db);
		$container->set('db_readonly', $this->db);

		// Instantiate the app
		$app = $container->get(App::class);

		// Register routes
		(require __DIR__ . '/../config/routes.php')($app);

		// Register middleware
		(require __DIR__ . '/../config/middleware.php')($app);

		return $app;
	}

	protected function getAppContainer()
	{
		try {
			return $this->getAppInstance()->getContainer();
		} catch (Exception $e) {
			echo $e;
		}
	}
}