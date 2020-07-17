<?php

include_once __DIR__ . '/../../tests/TestCase.php';
use App\Domain\Events\Repository\EventsRepository;

class EventsRepositoryTest extends Tests\TestCase
{
	private $eventsRepository;
	private $event_data;

	public function __construct()
	{
		parent::__construct();
		$container = $this->getAppContainer();
		$this->eventsRepository = new EventsRepository($container);
	}

	protected function setUp(): void
	{
		$this->event_data = [
			'event' 	=> 'This is a test event',
			'is_read'	=> 'no',
			'priority'	=> 'medium'
		];
	}

	public function testAddEventSuccess()
	{
		$event_id = $this->eventsRepository->addUpdateEvent($this->event_data, TRUE);
		$this->assertIsString($event_id);
		array_push($this->event_data, $event_id);

		//Check the ID is the correct inserted data
		$q = "SELECT * FROM events WHERE event_id = :event_id";
		$this->assertIsArray($this->db->dbFetch($q, ['event_id' => $event_id]));

		return $event_id;
	}

	/**
	 * @depends testAddEventSuccess
	 */
	public function testUpdateEventSuccess($event_id)
	{
		//Update the row
		$event_update = 'This is a test update';
		$this->event_data['event'] = $event_update;
		$this->assertTrue($this->eventsRepository->addUpdateEvent(array_merge($this->event_data, ['event_id' => $event_id])));

		//Check data was updated successfully
		$q = "SELECT `event` FROM `events` WHERE event_id = :event_id";
		$rslt = $this->db->dbFetch($q, ['event_id' => $event_id]);

		$this->assertEquals($event_update, $rslt['event'], 'Updated row does not match expected string');
	}

	public function testAddUpdateEventFailure()
	{
		$this->expectException(BadFunctionCallException::class);
		$this->eventsRepository->addUpdateEvent([]);
	}

	/**
	 * @depends testAddEventSuccess
	 */
	public function testCheckEventExistsSuccess($event_id)
	{
		$this->assertIsInt($this->eventsRepository->checkEventExists($event_id));

		//If successful, remove the test row
		$this->db->dbDelete('events', ['event_id' => $event_id]);
	}

	public function testCheckEventExistsFailure()
	{
		$this->expectException(BadFunctionCallException::class);
		$this->eventsRepository->checkEventExists([]);
	}

	public function testGetEvents()
	{
		//Check default call
		$this->assertIsArray($this->eventsRepository->getEvents(FALSE, NULL, FALSE));


	}

	public function testGetEventsCount()
	{
		$this->assertIsArray($this->eventsRepository->getEvents(FALSE, NULL, TRUE));

		$this->assertNotEquals('0', $this->eventsRepository->getEventsCount(FALSE, NULL), 'No events found. There must be events within the database for this test to pass.');
	}


}