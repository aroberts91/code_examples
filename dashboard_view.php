<?php
/**
 * @access_control yes
 * @description Staff Monitor
 * @image
 * @create yes
 * @read yes
 * @update yes
 * @delete yes
 * @extra pin,update_anyone
 * @administrator yes,yes,yes,yes,yes,yes
 * @supervisor yes,yes,yes,yes,no,no
 * @user no,no,no,no,no,no
 * @other no,no,no,no,no,no
 */
/**
 * monitor.php
 * @author Adam Roberts
 */
?>
<ul class="breadcrumbs">
	<li><a href="<?php echo site_url(); ?>">Dashboard</a></li>
	<span class="divider">/</span>
    <li><a href="<?php echo site_url() . '/monitor/dashboard.php'; ?>">Monitor</a></li>
    <span class="divider">/</span>
    <li class="active"><?php echo $page_title ?></li>
</ul>
<?php if( !$appt_data['staff'] && !$appt_data['client'] ) { ?>
    <div id="no-appts">
        No appointments today
    </div>
    <?php return; ?>
<?php } ?>
<div class="overview dashboard appt-container">
    <h1>Current Staff Status</h1>
    <div class="column9">
        <?php if( array_key_exists( 'late', $appt_data['staff'] ) ) { ?>
            <div id="staff-late" class="monitor-container-inner">
                <?php foreach( $appt_data['staff']['late'] as $appt ) { ?>
                    <div class="monitor-late card">
                        <?php echo $appt['image']['regular'] ?>
                        <span class="user-name display-block">
                            <b>
                                <?php echo $appt['name'] ?>
                            </b>
                        </span>
                        <span class="display-block">
                                Appointment time: <?php echo $appt['readable']['start_time'] ?>
                        </span>
                        <span class="display-block">
                                    Client: <?php echo $appt['client_firstname'] . ' ' . $appt['client_surname'] ?>
                        </span>
                        <span class="display-block appt-error-text">
                            <b><?php echo $appt['firstname'] ?> is late for this appointment!</b>
                        </span>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if( array_key_exists( 'no_check', $appt_data['staff'] ) ) { ?>
            <div id="staff-no-check-in-out" class="monitor-container-inner">
                <?php foreach( $appt_data['staff']['no_check'] as $appt ) { ?>
                    <div class="monitor-late card">
                        <?php echo $appt['image']['regular'] ?>
                        <span class="user-name display-block">
                        <b>
                            <?php echo $appt['name'] ?>
                        </b>
                        </span>
                        <span class="display-block">
                            Appointment time: <?php echo $appt['readable']['start_time'] ?>
                        </span>
                        <span class="display-block">
                                Client: <?php echo $appt['client_firstname'] . ' ' . $appt['client_surname'] ?>
                        </span>
                        <span class="display-block appt-error-text">
                            <b><?php echo $appt['firstname'] ?> has not checked in or out of this appointment!</b>
                        </span>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if( array_key_exists( 'warning', $appt_data['staff'] ) ) { ?>
            <div id="staff-warnings" class="monitor-container-inner">
                <?php foreach( $appt_data['staff']['warning'] as $appt ) { ?>
                    <div class="monitor-warning card">
                        <?php echo $appt['image']['large'] ?>
                        <span class="user-name display-block">
                        <b>
                            <?php echo $appt['name'] ?>
                        </b>
                        </span>
                        <span class="display-block">
                            Appointment time: <?php echo $appt['readable']['start_time'] ?>
                        </span>
                        <span class="display-block">
                                Client: <?php echo $appt['client_firstname'] . ' ' . $appt['client_surname'] ?>
                        </span>
                        <span class="display-block appt-warning-text">
                            <b><?php echo $appt['warning'] ?></b>
                        </span>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if( array_key_exists( 'no_issue', $appt_data['staff'] ) ) { ?>
            <div id="staff-no-issue" class="monitor-container-inner">
                <?php foreach( $appt_data['staff']['no_issue'] as $appt ) { ?>
                    <div class="monitor-no-issue card">
                        <?php echo $appt['image']['small'] ?>
                        <?php echo $appt['name'] ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
<div class="overview dashboard appt-container">
    <h1 class="overview-header">Daily Client Appointments</h1>
    <?php if( array_key_exists( 'late', $appt_data['client'] )) { ?>
        <div id="client-late" class="monitor-container-inner">
            <?php foreach( $appt_data['client']['late'] as $client_appts ) { ?>
                <?php if( count( $client_appts ) === 1 ) { ?>
                    <?php foreach( $client_appts as $appt ) { ?>
                        <div class="monitor-late card">
                            <?php echo $appt['image']['regular'] ?>
                            <span class="user-name display-block">
                                <b>
                                    <?php echo $appt['name'] ?>
                                </b>
                            </span>
                            <span class="display-block">
                                    Appointment time: <?php echo $appt['readable']['start_time'] ?>
                            </span>
                            <span class="display-block">
                                    Staff Member: <?php echo $appt['staff_firstname'] . ' ' . $appt['staff_surname'] ?>
                            </span>
                            <span class="display-block apt-error-text">
                                <b><?php echo $appt['warning'] ?></b>
                            </span>
                        </div>
                    <?php } ?>
                <?php }else { ?>
                    <div class="monitor-late card multi-issue">
                        <div class="issue-badge">
                            <span><?php echo sizeOf($client_appts) ?></span>
                        </div>
                        <?php foreach( $client_appts as $appt ) { ?>
                            <div class="monitor-inner">
                                <?php echo $appt['image']['regular'] ?>
                                <span class="user-name display-block">
                                <b>
                                    <?php echo $appt['name'] ?>
                                </b>
                                </span>
                                <span class="display-block">
                                        Appointment time: <?php echo $appt['readable']['start_time'] ?>
                                </span>
                                <span class="display-block">
                                        Staff Member: <?php echo $appt['staff_firstname'] . ' ' . $appt['staff_surname'] ?>
                                </span>
                                <span class="appt-error-text display-block">
                                    <b><?php echo $appt['warning'] ?></b>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
    <?php if( array_key_exists( 'no_check', $appt_data['client'] )) { ?>
        <div id="client-no-check-in-out" class="monitor-container-inner">
            <?php foreach( $appt_data['client']['no_check'] as $client_appts ) { ?>
                <?php if( count( $client_appts ) === 1 ) { ?>
                    <?php foreach( $client_appts as $appt ) { ?>
                        <div class="monitor-late card">
                            <?php echo $appt['image']['regular'] ?>
                            <span class="user-name display-block">
                                <b>
                                    <?php echo $appt['name'] ?>
                                </b>
                            </span>
                            <span class="display-block">
                                    Appointment time: <?php echo $appt['readable']['start_time'] ?>
                            </span>
                            <span class="display-block">
                                    Staff Member: <?php echo $appt['staff_firstname'] . ' ' . $appt['staff_surname'] ?>
                            </span>
                            <span class="appt-error-text display-block">
                                <b><?php echo $appt['warning'] ?></b>
                            </span>
                        </div>
                    <?php } ?>
                <?php }else { ?>
                    <div class="monitor-late card multi-issue">
                        <div class="issue-badge">
                            <span><?php echo sizeOf($client_appts) ?></span>
                        </div>
                            <?php foreach( $client_appts as $appt ) { ?>
                                <div class="monitor-inner">
                                    <?php echo $appt['image']['regular'] ?>
                                    <span class="user-name display-block">
                                    <b>
                                        <?php echo $appt['name'] ?>
                                    </b>
                                    </span>
                                        <span class="display-block">
                                            Appointment time: <?php echo $appt['readable']['start_time'] ?>
                                    </span>
                                        <span class="display-block">
                                            Staff Member: <?php echo $appt['staff_firstname'] . ' ' . $appt['staff_surname'] ?>
                                    </span>
                                        <span class="appt-error-text display-block">
                                        <b><?php echo $appt['warning'] ?></b>
                                    </span>
                                </div>
                            <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
    <?php if( array_key_exists( 'no_issue', $appt_data['client'] )) { ?>
		<div id="client-no-issue" class="monitor-container-inner">
			<?php foreach( $appt_data['client']['no_issue'] as $appt ) { ?>
				<div class="monitor-no-issue card">
                    <?php echo $appt['image']['small'] ?>
                    <?php echo $appt['name'] ?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
</div>
<script>
	//Split out each appointment section into different functions for readability
	function reloadAppointments( data ) {
		if( !data ) return;

		if( data.staff ) refresh_staff_appointments( data.staff );
		if( data.client ) refresh_client_appointments( data.client );
	}

	function refresh_staff_appointments( data ) {
		//Assign divs to variables for ease of use
        var staff_late = $('#staff-late');
        var staff_no_check = $('#staff-no-check-in-out');
        var staff_warnings = $('#staff-warnings');
        var staff_no_issue = $('#staff-no-issue');

		if( has_appts( data.late ) ) {
            var html = '';
            Object.values(data.late).map(( appt ) => {
                html += '<div class="monitor-late card">';
                html +=     appt.image.regular;
                html +=     '<span class="user-name display-block">';
                html +=         '<b>';
                html +=             appt.name;
                html +=         '</b>';
                html +=     '</span>';
                html +=     '<span class="display-block">';
                html +=         'Appointment time: ' + appt.readable.start_time;
                html +=     '</span>';
                html +=     '<span class="display-block">';
                html +=         'Client: ' + (appt.client_surname === '-' ? appt.client_surname : appt.client_firstname + ' ' + appt.client_surname);
                html +=     '</span>';
                html +=     '<span class="display-block appt-error-text">';
                html +=         '<b>' + appt.firstname + ' is late for this appointment!</b>';
                html +=     '</span>';
                html += '</div>';

            });
            staff_late.html( html );
        }

		if( has_appts( data.no_check ) ) {
            var html = '';
		    Object.values(data.no_check).map(( appt ) => {
		        html += '<div class="monitor-late card">';
		        html +=     appt.image.regular;
                html +=     '<span class="user-name display-block">';
                html +=         '<b>';
                html +=             appt.name;
                html +=         '</b>';
                html +=     '</span>';
                html +=     '<span class="display-block">';
                html +=         'Appointment time: ' + appt.readable.start_time;
                html +=     '</span>';
                html +=     '<span class="display-block">';
                html +=         'Client: ' + (appt.client_surname === '-' ? appt.client_surname : appt.client_firstname + ' ' + appt.client_surname);
                html +=     '</span>';
                html +=     '<span class="display-block appt-error-text">';
                html +=         '<b>' + appt.firstname + ' has not checked in or out of this appointment!</b>';
                html +=     '</span>';
                html += '</div>';

            });
		    staff_no_check.html( html );
        }

		if( has_appts( data.warning ) ) {
            var html = '';
            Object.values(data.warning).map(( appt ) => {
                html += '<div class="monitor-warning card">';
                html +=     appt.image.large;
                html +=     '<span class="user-name display-block">';
                html +=         '<b>';
                html +=             appt.name;
                html +=         '</b>';
                html +=     '</span>';
                html +=     '<span class="display-block">';
                html +=         'Appointment time: ' + appt.readable.start_time;
                html +=     '</span>';
                html +=     '<span class="display-block">';
                html +=         'Client: ' + (appt.client_surname === '-' ? appt.client_surname : appt.client_firstname + ' ' + appt.client_surname);
                html +=     '</span>';
                html +=     '<span class="display-block appt-warning-text">';
                html +=         '<b>' + appt.warning + '</b>';
                html +=     '</span>';
                html += '</div>';

            });
            staff_warnings.html( html );
        }

        if( has_appts( data.no_issue ) ) {
            var html = '';
            Object.values(data.no_issue).map(( appt ) => {
                html += '<div class="monitor-no-issue card">';
                html +=     appt.image.small;
                html +=     '<span class="user-name display-block">';
                html +=             appt.name;
                html +=     '</span>';
                html += '</div>';

            });
            staff_no_issue.html( html );
        }
	}

	function refresh_client_appointments( data ) {
	    //Assign divs to variables for ease of use
        var client_late = $('#client-late');
        var client_no_check = $('#client-no-check-in-out');
        var client_no_issue = $('#client-no-issue');

        if( has_appts( data.late ) ) {
            var html = '';
            Object.values(data.late).map(( appts ) => {
                appts.map(( appt ) => {
                    html += '<div class="monitor-late card">';
                    html +=     appt.image.regular;
                    html +=     '<span class="user-name display-block">';
                    html +=         '<b>';
                    html +=             appt.name;
                    html +=         '</b>';
                    html +=     '</span>';
                    html +=     '<span class="display-block">';
                    html +=         'Appointment time: ' + appt.readable.start_time;
                    html +=     '</span>';
                    html +=     '<span class="display-block">';
                    html +=         'Staff Member: ' + appt.staff_firstname + ' ' + appt.staff_surname;
                    html +=     '</span>';
                    html +=     '<span class="display-block appt-error-text">';
                    html +=         '<b>' + appt.warning + '</b>';
                    html +=     '</span>';
                    html += '</div>';
                });
            });
            client_late.html( html );
        }

        if( has_appts( data.no_check ) ) {
            var html = '';
            Object.values(data.no_check).map(( appts ) => {
                appts.map(( appt ) => {
                    html += '<div class="monitor-late card">';
                    html +=     appt.image.regular;
                    html +=     '<span class="user-name display-block">';
                    html +=         '<b>';
                    html +=             appt.name;
                    html +=         '</b>';
                    html +=     '</span>';
                    html +=     '<span class="display-block">';
                    html +=         'Appointment time: ' + appt.readable.start_time;
                    html +=     '</span>';
                    html +=     '<span class="display-block">';
                    html +=         'Staff Member: ' + appt.staff_firstname + ' ' + appt.staff_surname;
                    html +=     '</span>';
                    html +=     '<span class="display-block appt-error-text">';
                    html +=         '<b>' + appt.warning + '</b>';
                    html +=     '</span>';
                    html += '</div>';
                });
            });
            client_no_check.html( html );
        }

        if( has_appts( data.no_issue ) ) {
            var html = '';
            Object.values(data.no_issue).map(( appt ) => {
                html += '<div class="monitor-no-issue card">';
                html +=     appt.image.small;
                html +=     appt.name;
                html += '</div>';

            });
            client_no_issue.html( html );
        }
	}

	function has_appts( object ) {
	    //Check if the value is an object and contains data
	    if( typeof object === 'object' && object !== null && Object.keys( object ).length > 0 ) return true;

	    return false;
    }

    $.fn.nextOrFirst = function(selector)
    {
        var next = this.next(selector);
        return (next.length) ? next : this.prevAll(selector).last();
    };

    function onLoad() {
        // setTimeout(function() {
        // 	$.ajax({
        // 		url: 'load_appointments',
        // 		type: 'GET',
        // 		dataType: 'JSON',
        // 		data: { output: 'json' },
        // 		success: reloadAppointments
        // 	})
        // }, 150000);

        setInterval( () => {
            var issues = $('.multi-issue div.monitor-inner:visible' );

            for( ind = 0; ind < issues.length; ind++ ) {
                var div = $(issues[ind]);
                var next_div =  div.nextOrFirst('.monitor-inner');

                //THIS DOESNT WORK!!
                // div.fadeOut(500, function() {
                //     next_div.fadeIn();
                // });

                div.hide();
                next_div.fadeIn()
            }
        }, 5000);
    }


    $(document).ready( onLoad() );
</script>