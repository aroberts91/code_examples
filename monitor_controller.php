<?php


use phpDocumentor\Reflection\Types\Array_;

class Monitor extends MY_Controller
{
    private $possible_issues = [];
    private $late_notification;
    private $user_data;

    function __construct() {
        // Call the Model constructor
        parent::__construct();

        $this->load->driver('cache');
        $this->load->model([ 'monitor_model' ]);
        $this->load->library( [ 'get_directions' ]);
        $this->user_data = $this->default_data->get_default_data($_SESSION['user_id']);
        $this->late_notification    = $this->monitor_model->get_late_timer()->late_notification;

        $protocol = 'https://';
        $current_url = urlencode($protocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
        if (!check_login()) redirect("/login?url=" . $current_url);

		date_default_timezone_set('Europe/London');
    }

    public function overview(){
        //Load user data and default variables
        $data = $this->user_data;

        $data['page_title'] = 'Overview';
        $data['appt_data'] = $this->load_appointments();

        //Only an array can be parsed into a view
        //The data returns in a view as attributes
        $this->load->view('elements/header', $data);
        $this->load->view('monitor/overview', $data);
        $this->load->view('elements/footer', $data);
    }

	public function load_appointments( $output = 'html' ) {
		$staff_data = $this->monitor_model->load_staff_working();
    	$client_data = $this->monitor_model->load_client_appointments();
    	$appt_data = [ 'staff' => [], 'client' => [] ];

    	//Get staff appointments
        if( sizeof( $staff_data ) < 1 ) {
            $appt_data['staff'] = false;
        } 
        else {
            foreach( $staff_data as $staff ) {
                //Generate image URL and name for staff member
            	$this->get_image_url( $staff, 'staff' );

            	//Check staff appointments
                $this->check_previous_appointments( $staff, $appt_data );
                $this->check_current_appointments( $staff, $appt_data );
                $this->check_future_staff_appointments( $staff, $appt_data );
            }
        }

        //Get client appointments
        if( sizeof( $client_data ) < 1 ) {
            $appt_data['client'] = false;
        } 
        else {
            //Get client appointments
            foreach( $client_data as $client ) {
                //Convert appointment time to readable format, to be used later
                $client = $this->format_appt_times( $client );

                //Generate image URL and name for client
                $this->get_image_url( $client, 'client' );

                //Check client appointments
                $this->check_client_appointments( $client, $appt_data );
            }
        }

        //Move names higher within the array for easier sorting
        $this->get_names( $appt_data );

        if( $this->input->get('output') === 'json' ) {
            $appt_data['visit_term'] = lcfirst( VISIT_TERM );
        	$data['json_data'] = $appt_data;
			$this->load->view('elements/json_output', $data);
			return;
		}
        return $appt_data;
	}

	public function get_image_url( &$user, $type ) {
        if( $type === 'staff' ) {
            if(file_exists('/mnt/nfs/files/' . $this->db->database . '/uploads/staff_photo/' . $user['staff_id'] . '/' . $user['image'])) {
                $user['image_url']['large'] = '<img class="clone-remove user-image large" src="' . site_url('documents/view_document/uploads/staff_photo/' . $user['staff_id'] . '/' . $user['image']) . '" />';
                $user['image_url']['regular'] = '<img class="clone-remove user-image regular" src="' . site_url('documents/view_document/uploads/staff_photo/' . $user['staff_id'] . '/' . $user['image']) . '" />';
                $user['image_url']['small'] = '<img class="clone-remove user-image small" src="' . site_url('documents/view_document/uploads/staff_photo/' . $user['staff_id'] . '/' . $user['image']) . '" />';
            } 
            else {
                $user['image_url']['large'] = '<img class="clone-remove user-image large" src="' . base_url('css/images/profile_default.jpg') . '" alt="Image"  />';
                $user['image_url']['regular'] = '<img class="clone-remove user-image regular" src="' . base_url('css/images/profile_default.jpg') . '" alt="Image"  />';
                $user['image_url']['small'] = '<img class="clone-remove user-image small" src="' . base_url('css/images/profile_default.jpg') . '" alt="Image"  />';
            }

            //No longer needed
            unset($user['image']);
        } 
        elseif( $type === 'client' ) {
            //We replace the array key here as it's not touched later, unlike staff appointments
            $img_url = $user['image'];
            unset($user['image']);

            if(file_exists('/mnt/nfs/files/' . $this->db->database . '/uploads/client_photo/' . $user['client_id'] . '/' . $img_url)) {
                $user['image']['large'] = '<img class="clone-remove user-image large" src="' . site_url('documents/view_document/uploads/client_photo/' . $user['client_id'] . '/' . $img_url) . '" />';
                $user['image']['regular'] = '<img class="clone-remove user-image regular" src="' . site_url('documents/view_document/uploads/client_photo/' . $user['client_id'] . '/' . $img_url) . '" />';
                $user['image']['small'] = '<img class="clone-remove user-image small" src="' . site_url('documents/view_document/uploads/client_photo/' . $user['client_id'] . '/' . $img_url) . '" />';
            } 
            else {
                $user['image']['large'] = '<img class="clone-remove user-image large" src="' . base_url('css/images/profile_default.jpg') . '" alt="Image"  />';
                $user['image']['regular'] = '<img class="clone-remove user-image regular" src="' . base_url('css/images/profile_default.jpg') . '" alt="Image"  />';
                $user['image']['small'] = '<img class="clone-remove user-image small" src="' . base_url('css/images/profile_default.jpg') . '" alt="Image"  />';
            }
        }
	}

	public function check_client_appointments( $appointment, &$appt_data ) {
        $client_id  = $appointment['client_id'];
        $cur_time   = time();
        $appt_start = $appointment['start_time'];
        $appt_end   = $appointment['end_time'];
        $visit_term = lcfirst( VISIT_TERM );

        $staff_firstname    = $appointment['staff_firstname'];
        $staff_surname      = $appointment['staff_surname'];

        //Start by splitting out future appointments
        if( $cur_time < strtotime( $appt_start ) ) {
            //Check for possible late arrivals
            if( array_key_exists( 'no_check_in_out', $this->possible_issues ) ) {
                foreach( $this->possible_issues['no_check_in_out'] as $issue_appt ) {
                    if( $issue_appt['staff_id'] === $appointment['staff_id'] ) {
                        $appointment['warning_msg'] = "{$staff_firstname} {$staff_surname} did not arrive to a previous appointment.";
                        $appt_data['client'][$client_id]['warning'][] = $appointment;
                        return;
                    }
                }
            }

            if( array_key_exists( 'no_checkout', $this->possible_issues ) ) {
                foreach( $this->possible_issues['no_checkout'] as $issue_appt ) {
                    if( $issue_appt['staff_id'] === $appointment['staff_id'] ) {
                        $last_seen = '';
                        if( !$issue_appt['last_staff_booking_id'] ) {
                            $last_seen = "{$issue_appt['firstname']} has not been active today.";
                        }

                        if( $issue_appt['last_check_out_time'] ) {
                            $seen_datetime = new DateTime($issue_appt['last_check_out_time']);
                            $seen_time = $seen_datetime->format('g:ia');
                            $last_seen = "{$issue_appt['firstname']} was last seen checking out of an {$visit_term} at {$seen_time}.";
                        }

                        if( $issue_appt['last_check_in_time'] ) {
                            $seen_datetime = new DateTime( $issue_appt['last_check_in_time'] );
                            $seen_time = $seen_datetime->format('g:ia');
                            $last_seen = "{$issue_appt['firstname']} was last seen checking into an {$visit_term} at {$seen_time}.";
                        }

                        $issue_appt['warning_msg'] = "{$issue_appt['firstname']} did not check in/out of a previous {$visit_term}. {$last_seen}";
                        $appt_data['client'][$client_id]['warning'][] = $issue_appt;
                        return;
                    }
                }
            }

            if( array_key_exists( 'late_checkout', $this->possible_issues ) ) {
                foreach( $this->possible_issues['late_checkout'] as $issue_appt ) {
                    if( $issue_appt['staff_id'] === $appointment['staff_id'] ) {
                        $prob_late_leave = $this->get_late_time( $issue_appt['start_time'] );
                        $prob_checkout_time = $issue_appt['check_out_time'];

                        $now = new Datetime($issue_appt['check_out_time']);
                        $late_dt = new DateTime( '@' . $prob_late_leave );
						$diff = $now->getTimestamp() - $late_dt->getTimestamp();
                        $late_mins = $this->time_ago( $diff );

                        if(( !$issue_appt['postcode'] || !$appointment['postcode'] )) {
                            if((( strtotime( $appointment['start_time'] ) - strtotime( $issue_appt['check_out_time'] ))) <= (3600 + $late_mins) ) return;

                            $appointment['warning_msg'] = "{$appointment['staff_firstname']} was {$late_mins} late leaving a previous {$visit_term} and may arrive late.";
                            $appt_data['client'][$client_id]['warning'][] = $appointment;
                            return;
                        }

                        $travel_time = $this->get_directions->get( $issue_appt['postcode'], $appointment['postcode'] )['time'] / 60;
                        $time_over_appt = date('H:i:s', ( strtotime($prob_checkout_time) + $travel_time ) - (strtotime($appointment['start_time']) + strtotime($this->late_notification)));

                        if( $time_over_appt < 1 ) return;

                        $appointment['warning_msg'] = "{$appointment['staff_firstname']} was {$late_mins} late leaving a previous {$visit_term}. Travel times show that {$issue_appt['firstname']} could be {$time_over_appt} minutes late";
                        $appt_data['client'][$client_id]['warning'][] = $appointment;
                        return;
                    }
                }
            }
            
            if( array_key_exists( 'late_checkin', $this->possible_issues ) ) {
                foreach( $this->possible_issues['late_checkin'] as $issue_appt ) {
                    if( $issue_appt['staff_id'] === $appointment['staff_id'] ) {
                        $prob_late_arr = $this->get_late_time( $issue_appt['start_time'] );

                        if( !$issue_appt['check_in_time'] ) {
                            if( time() < $prob_late_arr ) return;
                            $now = new Datetime();
                            $late_dt = new DateTime( '@' . $prob_late_arr );
                            $diff = $now->getTimestamp() - $late_dt->getTimestamp();
                            $late_mins =  $this->time_ago( $diff );

                            $appointment['warning_msg'] = "{$appointment['staff_firstname']} is {$late_mins} late for a current {$visit_term} and has not yet arrived";
                            $appt_data['client'][$client_id]['warning'][] = $appointment;
                            return;
                        }
                        $checkin_dt = new DateTime( '@' . strtotime( $issue_appt['start_time'] ) );
						$late_dt = new DateTime( '@' . $prob_late_arr );
						$diff = $checkin_dt->getTimestamp() - $late_dt->getTimestamp();
						$late_mins =  $this->time_ago( $diff );
                        $appointment['warning_msg'] = "{$appointment['staff_firstname']} was {$late_mins} late arriving to a previous {$visit_term} and has not yet checked out.";
                        $appt_data['client'][$client_id]['warning'][] = $appointment;
                        break;
                    }
                }
            }
            
            if( $this->has_issues( $client_id, $appt_data, 'client' ) ) return;

			$appt_data['client'][$client_id]['no_issue'] = $appointment;
            return;
        }

        //Appointments past the start time
        if( $cur_time > $appt_start ) {
            //If the appointment isn't late, no issue
            if( $cur_time < $this->get_late_time( $appt_start ) ) {
                //Dont add to list if issues are apparent
                if( $this->has_issues( $client_id, $appt_data, 'client' ) ) return;

				$appt_data['client'][$client_id]['no_issue'] = $appointment;
                return;
            }

            //Did the staff member check in
            if( $appointment['check_in_time'] ) {
                //If the staff member has checked out this appointment is complete and we don't need to monitor it
                if( $appointment['check_out_time'] ) return;

				if( $cur_time < $appt_end ) {
                    if( $this->has_issues( $client_id, $appt_data, 'client' ) ) return;

					$appt_data['client'][$client_id]['no_issue'] = $appointment;
					return;
				}
            }

            //Staff member is late
            if( $cur_time < $appt_end ) {
                $appointment['warning_msg'] = "{$staff_firstname} {$staff_surname} is late for this {$visit_term}";
                $appt_data['client'][$client_id]['late'][] = $appointment;
                return;
            }

            //Staff member did not show at all
            $appointment['warning_msg'] = "{$staff_firstname} {$staff_surname} did not arrive for this {$visit_term}";
            $appt_data['client'][$client_id]['no_check'][] = $appointment;
        }
    }

	public function check_previous_appointments( $staff, &$appt_data ) {
        //No need to display this data, but we can use it to check for possible issues
		$staff_id = $staff['staff_id'];

		//This appointment is no longer relevant if the staff member has a current appointment
		if( !$staff['prev_staff_booking_id'] || $staff['cur_staff_booking_id'] ) return;

		//Populate appt data
		$prev_apt = array_merge( $this->get_staff_details( $staff ), $this->build_appt_arr( $staff, 'prev' ) );
		$prev_apt = $this->format_appt_times( $prev_apt );

		//Do we have a client name?
        if( !$prev_apt['client_firstname'] && !$prev_apt['client_surname'] ) {
            $prev_apt['client_surname'] = '-';
        }

		//Get the times this appointment would be late
		$late_arr = $this->get_late_time( $prev_apt['start_time'] );
		$late_leave = $this->get_late_time( $prev_apt['end_time'] );

		//Look at checkout first as checkin is obsolete if we have it
		if( $prev_apt['check_out_time'] ) {
			//No issue if checked out on time
			if( $prev_apt['check_out_time'] < $late_leave ) return;

			$this->possible_issues['late_checkout'][$staff_id] = $prev_apt;
			return;
		}

		//If they havent checked out, but have checked in this is still an issue
		if( $prev_apt['check_in_time'] ) {
			$this->possible_issues['no_checkout'][$staff_id] = $prev_apt;
		}

		//We know they didnt check in/out - this could be an issue
		$appt_data['staff'][$staff_id]['no_check'][] = $this->possible_issues['no_check_in_out'][ $staff_id ] = $prev_apt;
    }

	public function check_current_appointments( $staff, &$appt_data ) {
        $staff_id = $staff['staff_id'];

        //First check if staff member should be on an appointment currently
        if( !$staff['cur_staff_booking_id'] ) return;

        //Populate the appt data
        $cur_apt = array_merge($this->get_staff_details( $staff ), $this->build_appt_arr( $staff, 'cur' ));
        $cur_apt = $this->format_appt_times( $cur_apt );
        $late_time = $this->get_late_time( $cur_apt['start_time'] );

        //Do we have a client name?
        if( !$cur_apt['client_firstname'] && !$cur_apt['client_surname'] ) {
            $cur_apt['client_surname'] = '-';
        }

		//We don't need to worry about this appt yet
        if( time() < $late_time ) return;

        if( $cur_apt['check_in_time'] ) {
        	if( strtotime( $cur_apt['check_in_time'] ) <= $late_time ) {
        		//Staff member has checked in on time, no issues here
        		$this->remove_appt_issue( $staff_id );
        		return;
			}

			//Has the staff member checked out already (they could be cutting the appointment short to make up time)
			if( $cur_apt['check_out_time'] && ( strtotime( $cur_apt['check_out_time'] ) <= strtotime( $cur_apt['end_time'] ) ) ) {
				$appt_data['staff'][$staff_id]['no_issue'] = $cur_apt;

				//Remove any previous issues, we know they're okay at this point
				$this->remove_appt_issue($staff_id);
				return;
			}

				$appt_data['staff'][$staff_id]['late'][] = $this->possible_issues['late_checkin'][$staff_id] = $cur_apt;
				return;
		}

		//If staff member hasn't checked in over the late timer, we have an issue
		$appt_data['staff'][$staff_id]['late'][] = $this->possible_issues['late_checkin'][$staff_id] = $cur_apt;
		return;
    }

    public function check_future_staff_appointments( $staff, &$appt_data ) {
        $staff_id = $staff['staff_id'];
        $visit_term = lcfirst( VISIT_TERM );

        //No appointments for the rest of the day
        if( !$staff['fut_staff_booking_id'] ) return;

        if( array_key_exists( $staff_id, $appt_data['staff'] ) && array_key_exists( 'no_issue', $appt_data['staff'][$staff_id] ) ) return;

        //Populate the appt data
        $fut_apt = array_merge($this->get_staff_details( $staff ), $this->build_appt_arr( $staff, 'fut' ));
        $fut_apt = $this->format_appt_times( $fut_apt );

        if( !$fut_apt['client_firstname'] && !$fut_apt['client_surname'] ) {
            $fut_apt['client_surname'] = '-';
        }

        //If there are no previous issues
		if( sizeof( $this->possible_issues ) < 1 ) {
			$appt_data['staff'][$staff_id]['no_issue'] = $this->format_appt_times( $fut_apt );
			return;
		}

		$issue = $this->get_appt_issue( $staff_id );

		if( !$issue ) {
			$appt_data['staff'][$staff_id]['no_issue'] = $fut_apt;
			return;
		}

		$prob_app = $this->possible_issues[$issue][$staff_id];
        $prob_late_arr = $this->get_late_time( $prob_app['start_time'] );
		$prob_late_leave = $this->get_late_time( $prob_app['start_time'] );
        $prob_checkout_time = $prob_app['check_out_time'];

        $late_leave = $this->get_late_time( $fut_apt['end_time'] );

        //Multple ways for an appt to have an issue
		switch( $issue ) {
			case 'no_checkout':
				$fut_apt['warning_msg'] = "{$fut_apt['firstname']} did not check out of a previous {$visit_term} and may be late arriving";
				$appt_data['staff'][$staff_id]['warning'][] = $fut_apt;
				break;
			case 'no_check_in_out':
				$last_seen = '';
				if( !$fut_apt['last_staff_booking_id'] ) {
					$last_seen = "{$fut_apt['firstname']} has not been active today.";
				}

				if( $fut_apt['last_check_out_time'] ) {
					$seen_datetime = new DateTime($staff['last_check_out_time']);
					$seen_time = $seen_datetime->format('g:ia');
					$last_seen = "{$fut_apt['firstname']} was last seen checking out of an {$visit_term} at {$seen_time}.";
				}

				if( $fut_apt['last_check_in_time'] ) {
					$seen_datetime = new DateTime( $staff['last_check_in_time'] );
					$seen_time = $seen_datetime->format('g:ia');
					$last_seen = "{$fut_apt['firstname']} was last seen checking into an {$visit_term} at {$seen_time}.";
				}

				$fut_apt['warning_msg'] = "{$fut_apt['firstname']} did not check in/out of a previous {$visit_term}. {$last_seen}";
				$appt_data['staff'][$staff_id]['warning'][] = $fut_apt;
				break;
			case 'late_checkout':
				$now = new Datetime($prob_app['check_out_time']);
				$late_dt = new DateTime( '@' . $prob_late_leave );
                $diff = $now->getTimestamp() - $late_dt->getTimestamp();
                $late_mins =  $this->time_ago( $diff );

				if(( !$prob_app['postcode'] || !$fut_apt['postcode'] )) {
					if((( strtotime( $fut_apt['start_time'] ) - strtotime( $prob_app['check_out_time'] ))) <= (3600 + $late_mins) ) return;

					$fut_apt['warning_msg'] = "{$fut_apt['firstname']} was {$late_mins} late leaving a previous {$visit_term} and may arrive late.";
					$appt_data['staff'][$staff_id]['warning'][] = $fut_apt;
					return;
				}

				$travel_time = $this->get_directions->get( $prob_app['postcode'], $fut_apt['postcode'] )['time'] / 60;
				$time_over_appt = date('H:i:s', ( strtotime($prob_checkout_time) + $travel_time ) - (strtotime($fut_apt['start_time']) + strtotime($this->late_notification)));

				if( $time_over_appt < 1 ) return;

				$fut_apt['warning_msg'] = "{$fut_apt['firstname']} was {$late_mins} late leaving a previous {$visit_term}. Travel times show that {$fut_apt['firstname']} could be {$time_over_appt} minutes late";
				$appt_data['staff'][$staff_id]['warning'][] = $fut_apt;
				break;
			case 'late_checkin':
				if( !$prob_app['check_in_time'] ) {
					if( time() < $prob_late_arr ) return;
					$now = new Datetime();
					$late_dt = new DateTime( '@' . $prob_late_arr );
                    $diff = $now->getTimestamp() - $late_dt->getTimestamp();
                    $late_mins =  $this->time_ago( $diff );

                    $fut_apt['warning_msg'] = "{$fut_apt['firstname']} is {$late_mins} late for a current {$visit_term} and has not yet arrived";
					$appt_data['staff'][$staff_id]['warning'][] = $fut_apt;
					return;
				}
				$checkin_dt = new DateTime( '@' . strtotime( $prob_app['start_time'] ));
				$late_dt = new DateTime( '@' . $prob_late_arr );
				$diff = $late_dt->getTimestamp() - $checkin_dt->getTimestamp();
				$late_mins =  $this->time_ago( $diff );
				$fut_apt['warning_msg'] = "{$fut_apt['firstname']} was {$late_mins} late arriving to a previous {$visit_term} and has not yet checked out.";
				$appt_data['staff'][$staff_id]['warning'][] = $fut_apt;
				break;
		}
    }

    public function time_ago( $time ) {
        $mins = floor( $time / 60 );
        $hrs = floor( $time / 3600 );

        $time_ago = '';

        if( $hrs >= 1 ) {
            $time_ago = $hrs . ( $hrs >= 2 ? ' hours' : ' hour' );
        } else {
            $time_ago = $mins . ( $mins >= 2 ? ' minutes' : ' minute' );
        }

        return $time_ago;
    }

    public function remove_appt_issue( $staff_id ) {
		foreach( $this->possible_issues as $issue ) {
			if( !array_key_exists( $staff_id, $issue ) ) continue;

			unset( $this->possible_issues[ $issue ][ $staff_id ] ) ;
		}
		return;
	}

	public function get_staff_details( $user ) {
        return [
        	'staff_id' => $user['staff_id'],
            'firstname' => $user['firstname'],
            'surname' => $user['surname'],
            'image' => $user['image_url']
        ];
    }

    public function get_appt_issue( $staff_id ) {
		$keys = array_keys( $this->possible_issues );
		$issue = false;

		foreach( $keys as $key ) {
			if( !array_key_exists( $staff_id, $this->possible_issues[$key] ) ) continue;

			$issue = $key;
		}

		return $issue;
	}

	public function has_issues( $user_id, $appt_details, $type ) {
        //Check to see whether a user has outstanding issues, used before adding them to an OK list
        $appt_arr = $appt_details[$type];
        if(( array_key_exists( 'late', $appt_arr ) && array_key_exists( $user_id, $appt_arr['late'] )) || ( array_key_exists( 'no_check', $appt_arr ) && array_key_exists( $user_id, $appt_arr['no_check'] ) )) return true;

        return false;
    }

    public function format_appt_times( $appt ) {
    	$start_datetime = date_create($appt['start_time']);
        $start_time = date_format( $start_datetime, $this->user_data['user_data']['clock'] );

    	$end_datetime = date_create( $appt['end_time'] );
    	$end_time = date_format( $end_datetime, $this->user_data['user_data']['clock'] );

        $appt['readable']['start_time'] = $start_time;
        $appt['readable']['end_time'] = $end_time;

    	return $appt;
	}

    public function get_late_time( $time ) {
    	return strtotime( "+{$this->late_notification} minutes", strtotime( $time ) );
    }

	//Build an array of appointment data based on keys that match the provided string
    //Also strip column identifier from keys for ease of use in future
	public function build_appt_arr( $staff_data, $str ) {

        $keys = array_keys( $staff_data );
        $appt_data = [];

        foreach( $keys as $key ) {
            if( strpos( $key, $str ) !== false ) {
                $stripped_key = substr_replace( $key, '', 0, strlen( $str ) + 1 );
                $appt_data[$stripped_key] = $staff_data[$key];
            }

            if( strpos( $key, 'last' ) !== false ) {
            	$appt_data[$key] = $staff_data[$key];
			}
        }

        return $appt_data;
    }

    public function get_names( &$appt_data ) {
        //We use this function to move user names to the top of the multi-dimensional array. This is for easier sorting client side
		if( $appt_data['staff'] ) {
			foreach( $appt_data['staff'] as $appt ) {
				if( array_key_exists( 'no_check', $appt ) ) {
					$staff_id = $appt['no_check'][0]['staff_id'];
					$appt_data['staff'][$staff_id]['firstname'] = $appt['no_check'][0]['firstname'];
					$appt_data['staff'][$staff_id]['surname'] = $appt['no_check'][0]['surname'];
					continue;
				}

				if( array_key_exists( 'late', $appt ) ) {
					$staff_id = $appt['late'][0]['staff_id'];
					$appt_data['staff'][$staff_id]['firstname'] = $appt['late'][0]['firstname'];
					$appt_data['staff'][$staff_id]['surname'] = $appt['late'][0]['surname'];
					continue;
				}

				if( array_key_exists( 'warning',$appt ) ) {
					$staff_id = $appt['warning'][0]['staff_id'];
					$appt_data['staff'][$staff_id]['firstname'] = $appt['warning'][0]['firstname'];
					$appt_data['staff'][$staff_id]['surname'] = $appt['warning'][0]['surname'];
					continue;
				}

				if( array_key_exists('no_issue', $appt) ) {
					$staff_id = $appt['no_issue']['staff_id'];
					$appt_data['staff'][$staff_id]['firstname'] = $appt['no_issue']['firstname'];
					$appt_data['staff'][$staff_id]['surname'] = $appt['no_issue']['surname'];
					continue;
				}
			}
		}

		if( $appt_data['client'] ) {
			foreach( $appt_data['client'] as $appt ) {
				if( array_key_exists( 'no_check', $appt ) ) {
					$client_id = $appt['no_check'][0]['client_id'];
					$appt_data['client'][$client_id]['firstname'] = $appt['no_check'][0]['firstname'];
					$appt_data['client'][$client_id]['surname'] = $appt['no_check'][0]['surname'];
					continue;
				}

				if( array_key_exists( 'late', $appt ) ) {
					$client_id = $appt['late'][0]['client_id'];
					$appt_data['client'][$client_id]['firstname'] = $appt['late'][0]['firstname'];
					$appt_data['client'][$client_id]['surname'] = $appt['late'][0]['surname'];
					continue;
				}

				if( array_key_exists( 'warning',$appt ) ) {
					$client_id = $appt['warning'][0]['client_id'];
					$appt_data['client'][$client_id]['firstname'] = $appt['warning'][0]['firstname'];
					$appt_data['client'][$client_id]['surname'] = $appt['warning'][0]['surname'];
					continue;
				}

				if( array_key_exists('no_issue', $appt) ) {
					$client_id = $appt['no_issue']['client_id'];
					$appt_data['client'][$client_id]['firstname'] = $appt['no_issue']['firstname'];
					$appt_data['client'][$client_id]['surname'] = $appt['no_issue']['surname'];
					continue;
				}
			}
		}
    }
}