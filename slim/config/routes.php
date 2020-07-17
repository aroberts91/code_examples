<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
	$app->get('/', \App\Action\HomeAction::class);

	$app->group('/users', function (RouteCollectorProxy $group) {
		$group->get('', \App\Action\UserReadAction::class);
	});

	$app->group('/settings', function (RouteCollectorProxy $group) {
		$group->get('/', \App\Action\Settings::class . ':getSettings');
		$group->put('/', \App\Action\Settings::class . ':saveSettings');
		$group->post('/flush_settings', \App\Action\Settings::class . ':flushSettings');
		$group->put('/update_logo', \App\Action\Settings::class . ':updateLogo');
		$group->get('/get_headline_stats', \App\Action\Settings::class . ':getHeadlineStats');
	});

	$app->group('/events', function (RouteCollectorProxy $group) {
		$group->put('/', \App\Action\Events::class . ':addUpdateEvent');
		$group->get('/', \App\Action\Events::class . ':getEvents');
		$group->get('/check_event_exists', \App\Action\Events::class . ':checkEventExists');
		$group->get('/get_events_count', \App\Action\Events::class . ':getEventsCount');
	});

	$app->group('/preferences', function(RouteCollectorProxy $group) {
		$group->put('/', \App\Action\Preferences::class . ':addUpdatePreference');
		$group->get('/', \App\Action\Preferences::class . ':getPreferences');
		$group->get('/get_preference_data', \App\Action\Preferences::class . ':getPreferenceData');
		$group->get('/get_booking_preferences', \App\Action\Preferences::class . ':getBookingPreferences');
		$group->get('/get_matched_staff', \App\Action\Preferences::class . ':getMatchedStaff');
		$group->get('/get_visit_type_preferences', \App\Action\Preferences::class . ':getVisitTypePreferences');
		$group->get('/get_item', \App\Action\Preferences::class . ':getItem');
		$group->get('/get_items_by_type', \App\Action\Preferences::class . ':getItemsByType');
		$group->put('/add_update_preference_visit_assign', \App\Action\Preferences::class . ':addUpdatePreferenceVisitAssign');
		$group->put('/remove_staff_preferences', \App\Action\Preferences::class . ':removeStaffPreferences');
		$group->delete('/remove_preference_visit_assign', \App\Action\Preferences::class . 'removePreferenceVisitAssign');
	});
};