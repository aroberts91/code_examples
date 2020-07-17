<?php

namespace App\Domain\Events\Repository;

use App\Libraries\BaseRepository;
use BadFunctionCallException;
use Psr\Container\ContainerInterface;
/**
 * Events Repository (Model)
 */
final class EventsRepository extends BaseRepository
{
	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container The container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
	}

	/**
	 * Add or update an existing event
	 * @param array $data An array of columns to update and their vales
	 * @return bool Success or failure
	 */
	public function addUpdateEvent($data, $return_id = FALSE)
	{
		if( !$data || empty($data) ) {
			throw new BadFunctionCallException('No data provided');
		}

		if( array_key_exists('table', $data) ) {
			$table = $data['table'];
			unset($data['table']);
		} else {
			$table = 'events';
		}

		if( !$return_id ) {
			return $this->db->dbAddUpdate( $table, $data, 'event_id' );
		}

		$this->db->dbAddUpdate($table, $data, 'event_id' );
		return $this->db->lastInsertId();

	}

	/**
	 * Check whether an event with the given ID exists
	 * @param int $event_id The event ID to check
	 * @return bool Whether the event exists
	 */
	public function checkEventExists($event_id)
	{
		if(!$event_id) {
			throw new BadFunctionCallException('No event ID provided');
		}

		$q = "SELECT COUNT(event_id) AS event_count ";
		$q.= "FROM events ";
		$q.= "WHERE event_id = :event_id";

		return $this->db->dbFetch($q, ['event_id' => $event_id])['event_count'];
	}

	/**
	 * Return a list of all events
	 * @param bool $read Get read events, or all
	 * @param string $limit The time limit in the past to check e.g. 7 DAYS
	 * @param bool $count Whether to return a count, or the values
	 * @return mixed
	 */
	public function getEvents($read, $limit, $count)
	{
		$q = $count ? 'SELECT COUNT(*) num ' : 'SELECT * ';
		$q.= 'FROM events ';

		if(!is_null($limit) && !is_integer($limit)) {
			$q.= 'AND create_date > DATE_ADD(NOW(), INTERVAL -' . $limit . ') ';
		}

		$q.= 'AND is_read = ' . $read === FALSE ? 'no ' : 'yes ';

		if($limit && is_integer($limit)) {
			$q .= 'LIMIT 0, ' . $limit;
		}

		return $this->db->dbFetchAll($q, FALSE, FALSE);
	}

	/**
	 * Return a count of events
	 * @param bool $read Read events only or all
	 * @param string $limit Time limit in the past to check
	 * @return mixed
	 */
	public function getEventsCount($read, $limit)
	{
		return current($this->getEvents($read, $limit, TRUE)['num']);
	}
}
