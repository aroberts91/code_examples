<?php

require_once(__DIR__ . '/app_config.php');

// Error reporting
error_reporting(0);
ini_set('display_errors', '0');

//Set database name (to be used where container isn't available
$settings['memcache_server'] = MEMCACHE_IP;

// Timezone
date_default_timezone_set('Europe/London');

// Settings
$settings = [];

// Path settings
$settings['root'] = dirname(__DIR__);
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/public';

// Error Handling Middleware settings
$settings['error_handler_middleware'] = [

    // Should be set to false in production
    'display_error_details' => true,

    // Parameter is passed to the default ErrorHandler
    'log_errors' => true,

    // Display error details in error log
    'log_error_details' => true,
];


// Database settings
$settings['db'] = [
    'driver' 	=> 'mysql',
    'host' 		=> DATABASE,
    'username' 	=> 'root',
    'password' 	=> 'password',
    'flags' => [
        // Turn off persistent connections
        PDO::ATTR_PERSISTENT => false,
        // Enable exceptions
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Emulate prepared statements
        PDO::ATTR_EMULATE_PREPARES => false,
        // Set default fetch mode to array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		//Don't cast to string
		PDO::ATTR_STRINGIFY_FETCHES => false
    ],
];

$settings['db_readonly'] = $settings['db'];

return $settings;