<?php

namespace App\Domain\Preferences\Repository;

use App\Libraries\BaseRepository;
use App\Libraries\memcache_ext;
use BadFunctionCallException;
use Psr\Container\ContainerInterface;
/**
 * Preferences Repository (Model)
 */
final class PreferencesRepository extends BaseRepository
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

	public function getPreferences($data)
	{
		$order_by = array_key_exists('order_by', $data) ? $data['order_by'] : 'preference';
		$category = $data['category'];

		$q = "SELECT * ";
		$q.= "FROM preferences ";

		$data = [];

		if(!is_null($category)) {
			 $data['category'] =  $category;
			 $q.= "WHERE category = :category ";
		}

		$q.= "ORDER BY $order_by";

		$key = USER_DB . '_preferences_' . str_replace(' ', '_', $category);

		if(!$rslt = memcache_ext::domain_get($key)) {
			$rslt = $this->readonly_db->dbFetchAll($q, $data);
			memcache_ext::domain_add($key, $rslt, FALSE, 3600);
		}

		return $rslt;
	}

	public function addUpdatePreferences($data)
	{
		if(empty($data)) {
			throw new BadFunctionCallException('No preference data provided');
		}

		$table = array_key_exists('table', $data) ? $data['table'] : 'preference_careplan_assign';

		if( $table == 'preference_booking_assign' ) {
			$q = "SELECT * FROM `preferences` WHERE preference_id = :preference_id";
			$rslt = $this->db->dbFetch($q, $data['data']);

			if(!is_null($rslt)) {
				if(isset(json_decode($result['options'])['choices'][0]['alert'])) {
					$data['data']['alert'] = 1;
				}
			}
		}

		$rslt = $this->db->dbAddUpdate($table, $data['data'], 'preference_id');
		
		return is_null($data['data']['preference_id']) ? $this->db->lastInsertId() : $rslt;
	}

	public function getPreferenceData($data)
	{
		if( !array_key_exists('preference_id', $data) ) {
			throw new BadFunctionCallException('No preference ID provided');
		}

		if(!array_key_exists('client_id', $data)) $data['client_id'] = NULL;

		$q = "SELECT pr.options, pr.preference, pba.checked, pba.option_value, pba.message, bs.start_time ";
		$q.= "FROM preference_booking_assign pba, booking bo, booking_shift bs, preferences pr ";
		$q.= "WHERE pba.booking_id = bo.booking_id ";
		$q.= "AND bo.booking_id = bs.booking_id ";
		$q.= "AND pr.preference_id = pba.preference_id ";
		$q.= "AND pba.preference_id = :preference_id ";
		$q.= "AND bo.client_id = :client_id ";
		$q.= "ORDER BY bs.start_time;";
		
		return $this->readonly_db->dbFetchAll($q, [ 'preference_id' => $data['preference_id'], 'client_id' => $data['client_id'] ]);
	}

	public function getBookingPreferences($data)
	{
		if(!array_key_exists('booking_id', $data)) {
			throw new BadFunctionCallException('No booking ID provided');
		}

		//Bind the booking ID to be used multiple times in the next query
		$q = "SET @booking_id = :booking_id";
		$this->db->prepare($q)->execute(['booking_id' => $data['booking_id']]);

		if($data['count']) {
			$q = "SELECT COUNT(pr.preference_id) AS count ";
		} else {
			$q = "SELECT pr.preference_id, pr.preference, pr.category, pr.options, pr.`order`, pb.message, pb.alert ";
		}

		$q.= "FROM preferences pr ";
		$q.= "LEFT JOIN preference_visit_type_assign pv ON pr.preference_id = pv.preference_id ";
		$q.= "LEFT JOIN staff_booking sb ON sb.visit_type_id = pv.visit_type_id ";
		$q.= "LEFT JOIN booking_shift bs ON bs.booking_shift_id = sb.booking_shift_id ";
		$q.= "LEFT JOIN preference_booking_assign pb ON pb.preference_id = pv.preference_id AND pb.booking_id = @booking_id ";
		$q.= "WHERE bs.booking_id = @booking_id ";
		$q.= "AND (pb.preference_id IS NOT NULL OR pv.preference_id IS NOT NULL) ";
		$q.= "GROUP BY pr.preference_id ";
		$q.= "ORDER BY `order` ASC";

		$rslt =  $this->readonly_db->dbFetchAll($q, FALSE, FALSE);

		$booking_vals = [];

		if( $data['count'] ) {
			return $rslt[0]['count'];
		} else {
			foreach($rslt as $val) {
				if(json_decode($val['options'])['type'] != 'heading') {
					if($val['message'] != '') {
						array_push($booking_vals, $val);
					} else {
						if(!empty($pref_id)) $pref_id = $val['preference_id'];

						$type = json_decode($val['options'])['type'];

						if($type == 'info' ||$type == 'signature' || $type == 'checkbox' || $type == 'agree') {
							$pref_id = $val['preference_id'];

							$q = "SELECT * ";
							$q.= "FROM preferences pr ";
							$q.= "LEFT JOIN preference_visit_type_assign pv ON pr.preference_id = pv.preference_id ";
							$q.= "LEFT JOIN staff_booking sb ON sb.visit_type_id = pv.visit_type_id ";
							$q.= "LEFT JOIN booking_shift bs ON bs.booking_shift_id = sb.booking_shift_id ";
							$q.= "LEFT JOIN preference_booking_assign pb ON pb.preference_id = pv.preference_id ";
							$q.= "WHERE pr.preference_id = :preference_id ";
							$q.= "GROUP BY pr.preference_id ";
							$q.= "ORDER BY `order` ASC";

							$rslt = $this->db->dbFetch($q, ['preference_id' => $pref_id]);

							if(!is_null($rslt['message'])) {
								array_push($booking_vals, $rslt);
							} else {
								array_push($booking_vals, $val);
							}
						} else {
							$pref_id = $val['preference_id'];

							$q = "SELECT * ";
							$q.= "FROM preferences pr ";
							$q.= "LEFT JOIN preference_visit_type_assign pv ON pr.preference_id = pv.preference_id ";
							$q.= "LEFT JOIN staff_booking sb ON sb.visit_type_id = pv.visit_type_id ";
							$q.= "LEFT JOIN booking_shift bs ON bs.booking_shift_id = sb.booking_shift_id ";
							$q.= "LEFT JOIN preference_booking_assign pb ON pb.preference_id = pv.preference_id ";
							$q.= "WHERE pb.preference_id = :preference_id ";
							$q.= "GROUP BY pr.preference_id ";
							$q.= "ORDER BY `order` ASC";

							$rslt = $this->db->dbFetch($q, ['preference_id' => $pref_id]);

							if (isset($result) && !is_null($result)){
								array_push($booking_vals, $result);
							}
							else {
								array_push($booking_vals, $val);
							}
						}
					}
				} else {
					array_push($booking_vals, $val);
				}
			}
		}

		return $booking_vals;
	}

	public function getMatchedStaff($data)
	{
		if(!array_key_exists('careplan_id', $data) || $data['careplan_id'] === NULL) {
			throw new BadFunctionCallException('No Careplan ID provided');
		}

		if(!array_key_exists('day', $data) || $data['day'] === NULL) {
			throw new BadFunctionCallException('No Careplan day provided');
		}

		if(!array_key_exists('staff', $data) || $data['staff'] === NULL) {
			throw new BadFunctionCallException('No Staff ID provided');
		}

		$staff = implode(',', array_keys($data['staff']));
		$careplan_id = $data['careplan_id'];
		$day = $data['day'];

		$q = "SELECT COUNT(preference_id) AS preference_count";
		$q.= "FROM preference_careplan_assign pca ";
		$q.= "WHERE pca.careplan_id = :careplan_id ";
		$q.= "AND $day = 1";

		if($this->readonly_db->dbFetch($q, ['careplan_id' => $careplan_id])['preference_count']) {
			$q = "  SELECT staff_id, CONCAT_WS(' ', us.firstname, us.surname) AS staff
                        FROM (
                            SELECT COUNT(preference_id) AS staff_preferences, staff_id, (
                                SELECT COUNT(preference_id) FROM preference_careplan_assign pca WHERE pca.careplan_id = :careplan_id AND $day = 1
                            ) AS careplan_count
                            FROM preference_staff_assign psa 
                            WHERE 1
                            AND psa.preference_id IN (
                                SELECT preference_id FROM preference_careplan_assign pca WHERE pca.careplan_id = VALUES(careplan_id) AND $day = 1
                            )
                            AND psa.preference = 'yes'
                            GROUP BY psa.staff_id
                        ) sub, user us
                        WHERE staff_preferences = careplan_count ";
			$q.= "AND us.user_id = sub.staff_id ";
			$q.= "AND us.live = 'yes' ";
			$q.= "AND us.user_id IN($staff) ";
			$q.= "ORDER BY us.surname, us.firstname";

			return $this->readonly_db->dbFetchAll($q, ['careplan_id' => $careplan_id]);
		} else {
			$q = "SELECT us.user_id AS staff_id, CONCAT_WS(' ', us.firstname, us.surname) AS staff ";
			$q.= "FROM user us ";
			$q.= "WHERE us.live = 'yes' ";
			$q.= "AND us.user_id IN($staff)";

			return $this->readonly_db->dbFetchAll($q, FALSE, FALSE);
		}
	}

	public function getVisitTypePreferences($data)
	{
		if(!array_key_exists('visit_type_id', $data)) {
			throw new BadFunctionCallException('No Visit Type ID provided');
		}

		$q = "SELECT * ";
		$q.= "FROM preferences pr, preference_visit_type_assign pa ";
		$q.= "WHERE pr.preference_id = pa.preference_id ";
		$q.= "AND pa.visit_type_id = :visit_type_id ";
		$q.= "ORDER BY `order` ASC";

		return $this->readonly_db->dbFetchAll($q, ['visit_type_id' => $data['visit_type_id']]);
	}

	public function getItem($data)
	{
		if(!array_key_exists('preference_id', $data)) {
			throw new BadFunctionCallException('No preference ID provided');
		}

		$q = "SELECT * ";
		$q.= "FROM preferences ";
		$q.= "WHERE preference_id = :preference_id ";

		return $this->readonly_db->dbFetch($q, ['preference_id' => $data['preference_id']]);
	}

	public function getItemsByType($data)
	{
		if(!array_key_exists('value', $data)) {
			throw new BadFunctionCallException('No preference ID provided');
		}

		$q = "SELECT * ";
		$q.= "FROM preferences ";
		$q.= "WHERE `options` LIKE :options";

		return $this->readonly_db->dbFetchAll($q, ['options' => '%' . $data['value'] . '%']);
	}

	public function addUpdatePreferenceVisitAssign($data)
	{
		if(!array_key_exists('preferences', $data)) {
			throw new BadFunctionCallException('No preferences provided');
		}

		if(!array_key_exists('visit_type_id', $data)) {
			throw new BadFunctionCallException('No Visit Type ID provided');
		}

		$preferences = implode(',', $data['preferences']);

		//Don't use db functions as we're using an IN clause
		$q = "INSERT IGNORE INTO preference_visit_type_assign ";
		$q.= "SELECT :visit_type_id, preference_id ";
		$q.= "FROM preferences ";
		$q.= "WHERE preference_id IN ($preferences)";

		$this->db->prepare($q)->execute(['visit_type_id' => $data['visit_type_id']]);
	}

	public function removeStaffPreferences($data)
	{
		if(!array_key_exists('careplan_id', $data)) {
			throw new BadFunctionCallException('No Care plan ID provided');
		}

		if(!array_key_exists('staff_id', $data)) {
			throw new BadFunctionCallException('No Staff ID provided');
		}

		if(!array_key_exists('week', $data)) {
			throw new BadFunctionCallException('No Week provided');
		}

		if(!array_key_exists('day', $data)) {
			throw new BadFunctionCallException('No Day provided');
		}

		//Set day to NULL
		$rslt = $this->db->dbUpdate('careplan_staff_assign', [$data['day'] => NULL], ['careplan_id' => $data['careplan_id'], 'staff_id' => $data['staff_id'], 'week' => $data['weel']]);

		//Delete the row if all days are null
		$this->db->dbDelete('careplan_staff_assign', ['mon' => NULL, 'tue' => NULL, 'wed' => NULL, 'thu' => NULL, 'fri' => NULL, 'sat' => NULL, 'sun' => NULL]);

		return $rslt;
	}

	public function removePreferenceVisitAssign($data)
	{
		if(!array_key_exists('visit_type_id', $data)) {
			throw new BadFunctionCallException('No Visit Type ID provided');
		}

		//Don't use db functions as we're using an IN clause
		$q = "DELETE FROM preference_visit_type_assign ";
		$q.= "WHERE visit_type_id = :visit_type_id ";

		if(array_key_exists('preferences', $data) && !empty($data['preferences'])) {
			$preferences = implode(',', $data['preferences']);
			$q.= "AND preference_id NOT IN($preferences)";
		}

		return $this->db->prepare($q)->execute(['visit_type_id' => $data['visit_type_id']]);
	}
}
