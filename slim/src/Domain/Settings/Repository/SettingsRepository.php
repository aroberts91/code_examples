<?php


namespace App\Domain\Settings\Repository;

use App\Domain\Settings\Data\SettingsData;
use App\Libraries\BaseRepository;
use App\Libraries\memcache_ext;
use Psr\Container\ContainerInterface;
use DomainException;
use BadFunctionCallException;
use PDO;


/**
 * Settings Repository
 */

class SettingsRepository extends BaseRepository
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
	 * Return the settings key for the current system
	 *
	 * @return string The settings key
	 */
	private function getSettingsKey(): string
	{
		return hash('sha256', USER_DB . '_settings_' . COMMIT_ID );
	}

	/**
	 * Get user by the given user id
	 *
	 * @throws DomainException
	 *
	 * @return array The settings data
	 */
	public function getSettings(): array
	{
		$key = $this->getSettingsKey();
//		$rslt = memcache_ext::domain_get($key);
		$rslt = FALSE;

		$valid = isset($rslt['year_start_mon']) && isset($rslt['night_start']);

		if( !$rslt || !$valid ) {
			$sql = "SELECT * FROM settings WHERE setting_id = :setting_id;";
			$rslt = $this->readonly_db->dbFetch($sql, ['setting_id' => 1]);

			if(!$rslt) {
				throw new DomainException('Unable to load settings');
			}
			memcache_ext::domain_add($key, $rslt, FALSE, 3600);
		}

		//Build settings data object
		$settings = new SettingsData();
		foreach( $rslt as $key => $value ) {
			$settings->{$key} = $value;
		}

		return (array)$settings;
	}

	/**
	 * Flush the current settings from memcache
	 *
	 */
	public function flushSettings() {
		$key = $this->getSettingsKey();
		return memcache_ext::domain_delete($key);
	}

	/**
	 * Update settings in DB
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function saveSettings($data)
	{
		if(!$data || empty($data)) {
			throw new BadFunctionCallException('No settings data provided');
		}

		if( array_key_exists('table', $data) ) {
			$table = $data['table'];
			unset($data['table']);
		} else {
			$table = 'settings';
		}

		if( $table === 'mobile_setting' ) {
			return $this->db->dbUpdate($table, $data, []);
		}

		return $this->db->dbAddUpdate($table, $data, 'setting_id');
	}

	/**
	 * Update company logo
	 *
	 * @param string logo url
	 *
	 * @return bool
	 */
	public function updateLogo($logo)
	{
		if(!$logo || strlen($logo) < 10) {
			throw new BadFunctionCallException('No logo provided');
		}

		$sql = "SELECT company_logo FROM settings WHERE setting_id = :setting_id";
		$row = $this->readonly_db->dbFetch($sql, ['setting_id' => 1]);

		if( !empty($row['company_logo']) ) {
			@unlink(COMPANY_LOGO_UPLOAD_PATH . '/' . $row['company_logo']);
		}

		$rslt = $this->db->dbAddUpdate('settings', ['company_logo' => $logo], 'setting_id');

		if($rslt) {
			$key = hash('sha256', USER_DB . 'settings');
			memcache_ext::domain_delete($key);
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * 1point requires some summary statistics about systems
	 * @return array stats
	 */
	public function getHeadlineStats()
	{
		$q = "SELECT (SELECT COUNT(user_id) ";
		$q.= "FROM staff_personal_data spd ";
		$q.= "LEFT JOIN user u ON spd.staff_id = u.user_id ";
		$q.= "WHERE u.live = 'yes') active_users, ";
		$q.= "(SELECT COUNT(client_id) ";
		$q.= "FROM client c ";
		$q.= "WHERE c.discontinue_date IS NULL) active_service_users;";

		return $this->readonly_db->dbFetchAll($q, FALSE, FALSE);
	}
}