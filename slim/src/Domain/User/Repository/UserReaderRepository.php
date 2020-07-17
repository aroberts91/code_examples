<?php

namespace App\Domain\User\Repository;

use App\Domain\User\Data\UserData;
use Slim\App;
use DomainException;
use PDO;

/**
 * Repository.
 */
class UserReaderRepository
{
    /**
	 * @var PDO The readonly database
	 */
	private $readonly_db;

    /**
     * Constructor.
     *
     * @param App $app The database db
     */
    public function __construct(App $app)
    {
        $container = $app->getContainer();
        $this->readonly_db = $container->get('db_readonly');
    }

    /**
     * Get user by the given user id.
     *
     * @throws DomainException
     *
     * @return array The user data
     */
    public function getUsers(): array
    {
        $sql = "SELECT user_id, title, firstname, surname, email_address FROM user us;";
        $statement = $this->readonly_db->prepare($sql);
        $statement->execute();

        $rslt = $statement->fetchAll();

        if (!$rslt) {
            throw new DomainException('No users found.');
        }

        $response = [];

        foreach( $rslt as $row ) {
			// Map array to data object TODO: Create a mapping class to do this
			$user = new UserData();

			foreach( $row as $key => $value ) {
				$user->{$key} = $value;
			}

			$response[] = $user;
		}

        return $response;
    }
}
