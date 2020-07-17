<?php

namespace App\Domain\Settings\Data;

final class SettingsData
{
	/** @var int */
	public $setting_id;

	/** @var string */
	public $company_name;

	/** @var string */
	public $company_logo;

	/** @var string */
	public $company_address;

	/** @var string */
	public $telephone;

	/** @var string */
	public $telephone_ooh;

	/** @var string */
	public $company_email;

	/** @var string */
	public $company_reg_number;

	/** @var string */
	public $charity_number;

	/** @var string */
	public $vat_number;

	/** @var float */
	public $vat_rate;

	/** @var int */
	public $payment_timespan;

	/** @var string */
	public $payment_info;

	/** @var string */
	public $twilio_id;

	/** @var string */
	public $twilio_token;

	/** @var string */
	public $sms_number;

	/** @var string */
	public $staff_schedules;

	/** @var string */
	public $show_address;

	/** @var string */
	public $visit_rates;

	/** @var string */
	public $late_notifications;

	/** @var string */
	public $late_out_notifications;

	/** @var int */
	public $notification;

	/** @var int */
	public $notification_out;

	/** @var string */
	public $notification_schema;

	/** @var int */
	public $min_charging_unit;

	/** @var string */
	public $charge_rounding_policy;

	/** @var int */
	public $minimum_charge;

	/** @var int */
	public $minimum_pay;

	/** @var int */
	public $early_start;

	/** @var int */
	public $daytime_start;

	/** @var int */
	public $evening_start;

	/** @var int */
	public $night_start;

	/** @var string */
	public $package;

	/** @var string */
	public $booking_fee;

	/** @var string */
	public $booking_fee_per_sb;

	/** @var int */
	public $time_between_booking;

	/** @var float */
	public $staff_mileage_rate;

	/** @var float */
	public $client_mileage_rate;

	/** @var string */
	public $mileage_starting_location;

	/** @var string */
	public $paid_staff_travel;

	/** @var int */
	public $capped_pay;

	/** @var int */
	public $payroll_display_rounding;

	/** @var string */
	public $bank_holiday;

	/** @var string */
	public $staff_travel;

	/** @var string */
	public $invoice_p_type;

	/** @var string */
	public $invoice_print_format;

	/** @var string */
	public $careplan_print_type;

	/** @var string */
	public $week_start;

	/** @var string */
	public $assignment_print_template;

	/** @var string */
	public $xero_key;

	/** @var string */
	public $xero_secret;

	/** @var string */
	public $xero_invoice;

	/** @var string */
	public $nhs_number_type;

	/** @var string */
	public $cqc_ref;

	/** @var string */
	public $year_start_mon;

	/** @var int */
	public $year_start_day;

	/** @var int */
	public $call_monitor_space;

	/** @var string */
	public $availability;

	/** @var string */
	public $deleted_notes;

	/** @var int */
	public $enforce_location;

	/** @var string */
	public $daily_pdf_backup;

	/** @var string */
	public $client_schedule_addendum;

	/** @var string */
	public $staff_schedule_addendum;

	/** @var string */
	public $levels_enabled;

	/** @var string */
	public $subordination_enabled;

	/** @var int */
	public $travel_type_id;

	/** @var string */
	public $id_service_apikey;

	/** @var string */
	public $reconcile_on_invoice;

	/** @var string */
	public $alter_times_on_invoiced;

	/** @var string */
	public $holiday_year_start;

	/** @var DateTime */
	public $modified;

	/** @var string */
	public $archive_policy;

	/** @var float */
	public $cycling_range;

	/** @var float */
	public $walking_range;

	/** @var float */
	public $driving_range;
}
