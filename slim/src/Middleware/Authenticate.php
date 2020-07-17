<?php
namespace App\Middleware;

use App\Libraries\Database;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Selective\Config\Configuration;
use Slim\Psr7\Response;

class Authenticate
{
	/**
	 * @var ContainerInterface $container The container
	 */
	private $container;

	/**
	 * @var PDO The readonly database
	 */
	private $users_db;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;

		$config = $container->get(Configuration::class);
		$dsn = "mysql:host=" . $config->getString('db.host') . ";dbname=users;";
		$flags = $config->getArray('db_readonly.flags');

		$this->users_db =  new PDO($dsn, $config->getString('db.username'), $config->getString('db.password'), $flags);
	}

	public function __invoke(Request $request, RequestHandler $handler): Response
	{
		//Get the user email and API key
		if(!defined('USER_DB')) define('USER_DB', $_SERVER['PHP_AUTH_USER']);
		$key = $_SERVER['PHP_AUTH_PW'];

		$q = "SELECT id ";
		$q.= "FROM users.client_databases ";
		$q.= "WHERE db = :db AND api_key = :api_key;";

		$stmt = $this->users_db->prepare($q);
		$stmt->execute([
			'db' 	=> USER_DB,
			'api_key' => $key
		]);

		if( $stmt->rowCount() == 0 ) {
			header("HTTP/1.1 401 Unauthorized");
			die();
		}

		$this->container->set('db', function(ContainerInterface $container) {
			$config = $container->get(Configuration::class);

			$host = $config->getString('db.host');
			$username = $config->getString('db.username');
			$password = $config->getString('db.password');
			$flags = $config->getArray('db_readonly.flags');
			$dsn = "mysql:host=$host;dbname=" . USER_DB . ";";

			return new Database($dsn, $username, $password, $flags);
		});

		$this->container->set('db_readonly', function(ContainerInterface $container) {
			$config = $container->get(Configuration::class);

			$host = $config->getString('db_readonly.host');
			$username = $config->getString('db_readonly.username');
			$password = $config->getString('db_readonly.password');
			$flags = $config->getArray('db_readonly.flags');
			$dsn = "mysql:host=$host;dbname=" . USER_DB . ";";

			return new Database($dsn, $username, $password, $flags);
		});

		$response = $handler->handle($request);
		$existingContent = (string)$response->getBody();

		$response = new Response();
		$response->getBody()->write($existingContent);
		return $response->withHeader('Content-type', 'application/json');
	}
}