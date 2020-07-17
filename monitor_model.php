<?php


class monitor_model extends CI_Model
{
    public function __construct() {
        parent::__construct();
        $this->load->database();
        //selects into variables
    }

    public function load_staff_working(){
        //Get the details of the last appointment each staff member was active on
        $last_seen_q = "(SELECT sb.staff_id, sb.staff_booking_id,  cl.client_id, IF((bs.start_time BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()), 1, 0) overnight, cl.firstname, cl.surname, bo.booking_id, MAX(sb.actual_start_time) check_in_time, MAX(sb.actual_end_time) check_out_time, bs.start_time start_time, bs.end_time end_time, ad.postcode
            FROM staff_booking sb
            LEFT JOIN booking_shift bs ON bs.booking_shift_id = sb.booking_shift_id 
            LEFT JOIN booking bo ON bo.booking_id = bs.booking_id AND bo.venue_id IS NULL
            LEFT JOIN client cl ON cl.client_id = bo.client_id AND cl.client_id IS NOT NULL
            LEFT JOIN user us ON us.user_id = cl.client_id
            LEFT JOIN address ad ON ad.address_id = cl.address_id
            LEFT JOIN visit_type vt ON vt.visit_type_id = sb.visit_type_id 
            WHERE sb.staff_id IS NOT NULL 
            AND (bs.start_time BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)) AND (bs.end_time BETWEEN CURDATE() AND NOW()) 
            AND (sb.actual_start_time IS NOT NULL OR sb.actual_end_time IS NOT NULL)
            AND vt.`type` = 'assignment'
            AND us.live = 'yes'
            AND us.access_type <> 'No Access'
            GROUP BY sb.staff_id) last_seen ON last_seen.staff_id = sb.staff_id";

        //Get the details, if any, of the last appointment for each staff member
        $prev_app_q = "(SELECT sb.staff_id, sb.staff_booking_id,  cl.client_id, IF((bs.start_time BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()), 1, 0) overnight, cl.firstname, cl.surname, bo.booking_id, sb.actual_start_time check_in_time, sb.actual_end_time check_out_time, bs.start_time start_time, bs.end_time end_time, ad.postcode
            FROM staff_booking sb
            LEFT JOIN user us ON us.user_id = sb.staff_id
            LEFT JOIN booking_shift bs ON bs.booking_shift_id = sb.booking_shift_id
            LEFT JOIN booking bo ON bo.booking_id = bs.booking_id AND bo.venue_id IS NULL
            LEFT JOIN client cl ON cl.client_id = bo.client_id
            LEFT JOIN address ad ON ad.address_id = cl.address_id
            LEFT JOIN visit_type vt ON vt.visit_type_id = sb.visit_type_id 
            WHERE bs.end_time BETWEEN CURDATE() AND NOW()
			AND vt.`type` = 'assignment'
			AND us.live = 'yes'
			AND us.access_type <> 'No Access'
            GROUP BY sb.staff_id
            ORDER BY bs.end_time DESC) prev_app ON prev_app.staff_id = sb.stafF_id";

        //Get the details, if any, of the current appointment for each staff member
        $cur_app_q = "( SELECT sb.staff_id, sb.staff_booking_id,  cl.client_id, IF((bs.start_time BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()), 1, 0) overnight, cl.firstname, cl.surname, bo.booking_id, sb.actual_start_time check_in_time, sb.actual_end_time check_out_time, bs.start_time start_time, bs.end_time end_time, ad.postcode
            FROM staff_booking sb
            LEFT JOIN user us ON us.user_id = sb.staff_id
            LEFT JOIN booking_shift bs ON bs.booking_shift_id = sb.booking_shift_id
            LEFT JOIN booking bo ON bo.booking_id = bs.booking_id AND bo.venue_id IS NULL
            LEFT JOIN client cl ON cl.client_id = bo.client_id
            LEFT JOIN address ad ON ad.address_id = cl.address_id
            LEFT JOIN visit_type vt ON vt.visit_type_id = sb.visit_type_id 
            WHERE NOW() BETWEEN bs.start_time AND bs.end_time
            AND vt.`type` = 'assignment'
            AND us.live = 'yes'
            AND us.access_type <> 'No Access'
            GROUP BY sb.staff_id) cur_app ON cur_app.staff_id = sb.staff_id";

        //Get the details, if any, of the next appointment for each staff member
        $fut_app_q = "( SELECT sb.staff_id, sb.staff_booking_id,  cl.client_id, IF((bs.start_time BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()), 1, 0) overnight, cl.firstname, cl.surname, bo.booking_id, sb.actual_start_time check_in_time, sb.actual_end_time check_out_time, bs.start_time start_time, bs.end_time end_time, ad.postcode
            FROM staff_booking sb
            LEFT JOIN user us ON us.user_id = sb.staff_id
            LEFT JOIN booking_shift bs ON bs.booking_shift_id = sb.booking_shift_id
            LEFT JOIN booking bo ON bo.booking_id = bs.booking_id AND bo.venue_id IS NULL
            LEFT JOIN client cl ON cl.client_id = bo.client_id
            LEFT JOIN address ad ON ad.address_id = cl.address_id
            LEFT JOIN visit_type vt ON vt.visit_type_id = sb.visit_type_id
            WHERE bs.start_time BETWEEN NOW() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            AND vt.`type` = 'assignment'
            AND us.live = 'yes'
            AND us.access_type <> 'No Access'
            GROUP BY sb.staff_id
            ORDER BY bs.end_time ASC) fut_app ON fut_app.staff_id = sb.staff_id";

        $q = "SELECT sb.staff_id, IF(LENGTH(us.firstname) < 20, us.firstname, SUBSTRING(us.firstname, 1, 1)) firstname, us.surname, us.image,
            last_seen.staff_booking_id last_staff_booking_id, last_seen.client_id last_client_id, last_seen.overnight last_client_overnight, last_seen.firstname last_client_firstname, last_seen.surname last_client_surname, last_seen.booking_id last_booking_id, last_seen.check_in_time last_check_in_time, last_seen.check_out_time last_check_out_time, last_seen.start_time last_start_time, last_seen.end_time last_end_time, last_seen.postcode last_postcode,
            prev_app.staff_booking_id prev_staff_booking_id, prev_app.client_id prev_client_id, prev_app.overnight prev_client_overnight, prev_app.firstname prev_client_firstname, prev_app.surname prev_client_surname, prev_app.booking_id prev_booking_id, prev_app.check_in_time prev_check_in_time, prev_app.check_out_time prev_check_out_time, prev_app.start_time prev_start_time, prev_app.end_time prev_end_time, prev_app.postcode prev_postcode,
            cur_app.staff_booking_id cur_staff_booking_id, cur_app.client_id cur_client_id, cur_app.overnight cur_client_overnight, cur_app.firstname cur_client_firstname, cur_app.surname cur_client_surname, cur_app.booking_id cur_booking_id, cur_app.check_in_time cur_check_in_time, cur_app.check_out_time cur_check_out_time, cur_app.start_time cur_start_time, cur_app.end_time cur_end_time, cur_app.postcode prev_postcode,
            fut_app.staff_booking_id fut_staff_booking_id, fut_app.client_id fut_client_id, fut_app.overnight fut_client_overnight, fut_app.firstname fut_client_firstname, fut_app.surname fut_client_surname, fut_app.booking_id fut_booking_id, fut_app.check_in_time fut_check_in_time, fut_app.check_out_time fut_check_out_time, fut_app.start_time fut_start_time, fut_app.end_time fut_end_time, fut_app.postcode prev_postcode 
            FROM staff_booking sb
            LEFT JOIN booking_shift bs ON bs.booking_shift_id = sb.booking_shift_id
            LEFT JOIN booking bo ON bo.booking_id = bs.booking_id AND bo.venue_id IS NULL
            LEFT JOIN visit_type vt ON vt.visit_type_id = sb.visit_type_id 
            LEFT JOIN user us ON sb.staff_id = us.user_id
            LEFT JOIN " . $last_seen_q . "
            LEFT JOIN " . $prev_app_q . "
            LEFT JOIN " . $cur_app_q . "
            LEFT JOIN " . $fut_app_q . "
            WHERE sb.staff_id IS NOT NULL 
            AND ((bs.start_time BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)) OR (bs.end_time BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)))
			AND vt.`type` = 'assignment'
			AND us.live = 'yes'
			AND us.access_type <> 'No Access'
            GROUP BY sb.staff_id";


        $result = $this->db->query($q)->result_array();
        return $result;
    }

    public function load_client_appointments() {
        $q = "SELECT cl.client_id, cl.firstname firstname, cl.surname surname, cl.logo image, bs.start_time, bs.end_time, sb.staff_id, sb.actual_start_time check_in_time, sb.actual_end_time check_out_time, IF(LENGTH(us.firstname) < 20, us.firstname, SUBSTRING(us.firstname, 1, 1)) staff_firstname, us.surname staff_surname, ad.postcode
            FROM client cl
            LEFT JOIN booking bo ON bo.client_id = cl.client_id AND bo.venue_id IS NULL
            LEFT JOIN booking_shift bs ON bs.booking_id = bo.booking_id
            LEFT JOIN staff_booking sb ON sb.booking_shift_id = bs.booking_shift_id
            LEFT JOIN user us ON us.user_id = sb.staff_id
            LEFT JOIN visit_type vt ON vt.visit_type_id = sb.visit_type_id 
            LEFT JOIN venue v ON v.client_id = cl.client_id 
            LEFT JOIN address ad ON ad.address_id = v.address_id
            WHERE ((bs.start_time BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) OR bs.end_time BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY))) 
            AND cl.client_id IS NOT NULL
            AND (cl.company_name IS NULL OR cl.company_name = 'active') 
            AND sb.staff_id IS NOT NULL
            AND ad.monitoring <> 'none'
            AND us.live = 'yes'
            AND us.access_type <> 'No Access'
            AND vt.`type` = 'assignment'";

        $result = $this->db->query($q)->result_array();
        return $result;
    }

    public function get_late_timer() {
    	$q = "SELECT IF(late_notifications = 'on', notification, 30) late_notification
			FROM settings";

    	$result = $this->db->query($q)->row();
    	return $result;
	}
}