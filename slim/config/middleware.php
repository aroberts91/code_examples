<?php

require_once (__DIR__ . '/constants.php');

use App\Middleware\Authenticate;
use Psr\Container\ContainerInterface;
use Selective\Config\Configuration;
use Slim\App;
use App\Libraries\memcache_ext;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add global middleware to app
    $app->addRoutingMiddleware();

    $container = $app->getContainer();
	$app->add(new Authenticate($container));

	//Instantiate the memcache connection (incase we're not using a set)
	memcache_ext::cache();

	// Error handler
    $settings = $container->get(Configuration::class)->getArray('error_handler_middleware');
    $display_error_details = (bool)$settings['display_error_details'];
    $log_errors = (bool)$settings['log_errors'];
    $log_error_details = (bool)$settings['log_error_details'];

    $app->addErrorMiddleware($display_error_details, $log_errors, $log_error_details);
};
