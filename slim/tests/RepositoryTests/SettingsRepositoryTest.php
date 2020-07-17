<?php

include_once __DIR__ . '/../../tests/TestCase.php';
use App\Domain\Settings\Repository\SettingsRepository;

class SettingsRepositoryTest extends Tests\TestCase
{
	private $settingsRepository;

	public function __construct()
	{
		parent::__construct();
		$container = $this->getAppContainer();

		$this->settingsRepository = new SettingsRepository($container);
	}

	public function testGetSettings()
	{
		$this->assertIsArray($this->settingsRepository->getSettings(), 'Settings array not found');
	}

	public function testSaveSettingsFailure()
	{
		$data = [];
		$this->expectException(BadFunctionCallException::class);
		$this->settingsRepository->saveSettings($data);
	}

	public function testSaveSettingsSuccess()
	{
		//Get the current notification setting
		$sql = "SELECT late_out_notifications FROM settings WHERE setting_id = 1";
		$rslt = $this->db->dbFetch($sql, FALSE, FALSE);

		$data = ['late_out_notifications' => $rslt['late_out_notifications']];

		$this->assertTrue($this->settingsRepository->saveSettings($data), 'Settings were not updated as expected');
	}

	public function testUpdateLogoFailure()
	{
		$this->expectException(BadFunctionCallException::class);
		$this->settingsRepository->updateLogo('');
	}

	public function testUpdateLogoSuccess()
	{
		$container = $this->getAppContainer();

		$sql = "SELECT company_logo FROM settings WHERE setting_id = 1";
		$row = $this->db->dbFetch($sql, FALSE, FALSE);

		$logo = strlen($row['company_logo']) > 1 ? $row['company_logo'] : '/test/test';
		$this->assertTrue($this->settingsRepository->updateLogo($logo), 'Logo was not updated as expected');
	}

	public function testGetHeadlineStats()
	{
		$this->assertIsArray($this->settingsRepository->getHeadlineStats(), 'Headline stats not returned correctly');
	}

}