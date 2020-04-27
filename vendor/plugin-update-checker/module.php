<?php
///https://github.com/YahnisElsts/plugin-update-checker#how-to-release-an-update-1
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/Danone89/wordpress-helpers',
	__FILE__,
	'wordpress_helpers'
);