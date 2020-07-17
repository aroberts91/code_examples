<?php

$config['app_config_file'] = '/mnt/nfs/files/carefor/app_config.php';

// recache file
$nfs_file = file_exists($config['app_config_file']) ? $config['app_config_file'] : '/mnt/nfs/files/carefor/app_config.php';

// load from cache!
//require_once($nfs_file); //TODO: Add cache

define("C4IT_VERSION", strtoupper("beta"));
$commit_path = dirname(dirname(dirname(__FILE__))) . "/commit.id";
define("COMMIT_ID", file_exists($commit_path) ? file_get_contents($commit_path) : "DEV");

// rackspace opencloud connectivity
define("KEY_RACKSPACE", "0884b7fe0e645c8460334188380f06b2");
define("REGION_RACKSPACE", "LON");
define("USERNAME_RACKSPACE", "webformed");
