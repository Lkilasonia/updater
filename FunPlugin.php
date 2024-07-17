<?php
/**
* Plugin Name: Updater
* Plugin URI: https://github.com/Lkilasonia/updater
* Description: This is a Fun Plugin.
* Version: 3.0
* Author: Lasha Kilasonia
* Author URI: https://elementar.ge
**/

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/Lkilasonia/updater',
	__FILE__,
	'FunPlugin'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');

//Optional: If you're using a private repository, specify the access token like this:
$myUpdateChecker->setAuthentication('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');
