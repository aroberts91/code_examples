<?php

include_once __DIR__ . '/../../tests/TestCase.php';
use App\Domain\Preferences\Repository\PreferencesRepository;

class PreferencesRepositoryTest extends Tests\TestCase
{
	private $preferencesRepository;

	public function __construct()
	{
		parent::__construct();
		$container = $this->getAppContainer();
		$this->preferencesRepository = new PreferencesRepository($container);
	}

	public function createTestRows()
	{
		//Test creating a row first
		$data = [
			'data' => [
				'preference' 			=> 'Test preference',
				'category'				=> 'Personal Care',
				'options'				=> json_encode(['1' => 'yes', '2' => 'no']),
				'placeholder'			=> 'test',
				'text_show'				=> 'test',
				'send_notification'		=> 'no',
				'notification_message'	=> NULL
			],
			'table' => 'preferences'
		];

		//Insert a new row, get the ID and confirm it was added successfully
		$preference_id = $this->preferencesRepository->addUpdatePreferences($data);
		$this->assertIsString($preference_id, 'Failed to insert new row');

		//Insert a mock visit type
		$this->db->dbAddUpdate('visit_type', ['visit_type' => 'Test visit type', 'colour' => '#ECECEC']);
		$visit_type_id = $this->db->lastInsertId();

		//Link the test rows
		$this->db->dbAddUpdate('preference_visit_type_assign', ['visit_type_id' => $visit_type_id, 'preference_id' => $preference_id]);


		//If successful, return ID
		$data['data']['preference_id'] = $data['test_data']['preference_id'] = $preference_id;
		$data['test_data']['visit_type_id'] = $visit_type_id;
		return $data;
	}

	public function deleteTestRows($data)
	{
		//Remove the test row
		$this->db->dbDelete('preferences', ['preference_id' => (int)$data['preference_id']]);
		$this->db->dbDelete('visit_type', ['visit_type_id' => (int)$data['visit_type_id']]);
	}

	public function testGetPreferences()
	{
		$this->assertIsArray($this->preferencesRepository->getPreferences([]), 'Preferences array not found');
	}

	public function testAddUpdatePreferencesSuccess()
	{
		$data = $this->createTestRows();

		//Begin by editing a column
		$data['data']['preference'] = 'Updated successfully';
		$insert_id = $data['data']['preference_id'];
		$test_data = $data['test_data'];

		$this->assertTrue($this->preferencesRepository->addUpdatePreferences($data), 'Failed to update existing row');

		//Finally, check the updated row has changed successfully
		$sql = "SELECT preference FROM preferences WHERE preference_id = :preference_id";

		$rslt = $this->db->dbFetch($sql, ['preference_id' => $insert_id]);

		$this->assertEquals('Updated successfully', $rslt['preference'], 'Update returned successfully but data not updated as expected');

		$this->deleteTestRows($test_data);
	}

	public function testAddUpdatePreferenceFailure()
	{
		$this->expectException(BadFunctionCallException::class);
		$this->preferencesRepository->addUpdatePreferences([]);
	}

	public function testGetPreferenceDataSuccess()
	{
		$data = $this->createTestRows();

		//Manually get the first row which should successfully test the function
		$q.= "SELECT bo.client_id, pba.preference_id ";
		$q.= "FROM preference_booking_assign pba, booking bo, booking_shift bs, preferences pr ";
		$q.= "WHERE pba.booking_id = bo.booking_id ";
		$q.= "AND bo.booking_id = bs.booking_id ";
		$q.= "AND pr.preference_id = pba.preference_id ";

		$rslt = $this->db->dbFetch($q, FALSE, FALSE);

		//Now test the function
		$this->assertIsArray($this->preferencesRepository->getPreferenceData($rslt), 'No preference data returned');

		$this->deleteTestRows($data['test_data']);
	}

	public function testGetPreferenceDataFailure()
	{
		$this->expectException(BadFunctionCallException::class);
		$this->preferencesRepository->getPreferenceData([]);
	}

	public function testGetBookingPreferencesSuccess()
	{
		$data = $this->createTestRows();

		//Manually get the first row which should successfully test the function
		$q = "SELECT bs.booking_id ";
		$q.= "FROM preference_visit_type_assign pv ";
		$q.= "LEFT JOIN preferences pr ON pr.preference_id = pv.preference_id ";
		$q.= "LEFT JOIN staff_booking sb ON sb.visit_type_id = pv.visit_type_id ";
		$q.= "LEFT JOIN booking_shift bs ON bs.booking_shift_id = sb.booking_shift_id ";
		$q.= "LEFT JOIN preference_booking_assign pb ON pb.booking_id = bs.booking_id AND pb.preference_id = pv.preference_id ";
		$q.= "GROUP BY pr.preference_id ";
		$q.= "ORDER BY `order` ASC";

		$rslt = $this->db->dbFetch($q, FALSE, FALSE);

		$this->assertIsArray($this->preferencesRepository->getBookingPreferences($rslt));

		$this->deleteTestRows($data['test_data']);
	}

	public function testGetBookingPreferencesFailure()
	{
		$this->expectException(BadFunctionCallException::class);
		$this->preferencesRepository->getBookingPreferences([]);
	}

	//TODO: add test for getMatchedStaff

	public function testGetVisitTypePreferencesSuccess() {
		$data = $this->createTestRows();

		//Manually get the first row which should successfully test the function
		$q = "SELECT pa.visit_type_id ";
		$q.= "FROM preferences pr, preference_visit_type_assign pa ";
		$q.= "WHERE pr.preference_id = pa.preference_id ";
		$q.= "ORDER BY `order` ASC";

		$rslt = $this->db->dbFetch($q, FALSE, FALSE);
		
		$this->assertIsArray($this->preferencesRepository->getVisitTypePreferences($rslt));

		$this->deleteTestRows($data['test_data']);
	}

	public function testGetVisitTypePreferencesFailure() {
		$this->expectException(BadFunctionCallException::class);
		$this->assertIsArray($this->preferencesRepository->getVisitTypePreferences([]));
	}
	
	public function testGetItemSuccess()
	{
		//Manually get the first row which should successfully test the function
		$data = $this->createTestRows();

		$this->assertIsArray($this->preferencesRepository->getItem($data['data']), 'Preference item could not be found');

		$this->deleteTestRows($data['test_data']);
	}

	public function testGetItemFailure()
	{
		$this->expectException(BadFunctionCallException::class);
		$this->assertIsArray($this->preferencesRepository->getItem([]));
	}

	//We can't test addUpdatePreferenceVisitAssign success as it has no return value
	public function testAddUpdatePreferenceVisitAssignFailure()
	{
		$this->expectException(BadFunctionCallException::class);
		$this->assertIsArray($this->preferencesRepository->addUpdatePreferenceVisitAssign([]));
	}

	public function testRemoveStaffPreferencesSuccess()
	{
		//Get a row to test with
		$q = "SELECT careplan_id, staff_id, week FROM careplan_staff_assign";
		$rslt = $this->db->dbFetch($q, FALSE, FALSE);

		//Make sure monday is set
		$this->db->dbUpdate('careplan_staff_assign', ['mon' => 1], ['careplan_id' => $rslt['careplan_id']]);

		$rslt['day'] = 'mon';

		$this->assertTrue($this->preferencesRepository->removeStaffPreferences($rslt));
	}

	public function testRemoveStaffPreferencesFailure()
	{
		$this->expectException(BadFunctionCallException::class);
		$this->assertIsArray($this->preferencesRepository->removeStaffPreferences([]));
	}

	public function testRemovePreferencevisitAssignSuccess()
	{
		//Manually create a record to work with
		$q = "SELECT * FROM preference_visit_type_assign";
		$rslt = $this->db->dbFetch($q, FALSE, FALSE);

		$this->assertTrue($this->preferencesRepository->removePreferenceVisitAssign(['visit_type_id' => $rslt['visit_type_id']]));
	}

	public function testRemovePreferencevisitAssignFailure()
	{
		$this->expectException(BadFunctionCallException::class);
		$this->assertIsArray($this->preferencesRepository->removePreferenceVisitAssign([]));
	}
}