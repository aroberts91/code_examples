<?php

use DI\ContainerBuilder;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

$container_builder = new ContainerBuilder();

// Set up settings
$container_builder->addDefinitions(__DIR__ . '/container.php');

// Build PHP-DI Container instance
$container = $container_builder->build();

// Create App instance
$app = $container->get(App::class);

// Register routes
(require __DIR__ . '/routes.php')($app);

// Register middleware
(require __DIR__ . '/middleware.php')($app);

return $app;
