<?php
/**
* Plugin Name: Updater
* Plugin URI: https://github.com/Lkilasonia/updater
* Description: This is a Fun Plugin.
* Version: 2.0
* Author: Lasha Kilasonia
* Author URI: https://elementar.ge
**/

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/Lkilasonia/updater',
	__FILE__,
	'FunPlugin'
);

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');

// Optional: If you're using a private repository, specify the access token
$myUpdateChecker->setAuthentication(getenv('GITHUB_ACCESS_TOKEN'));