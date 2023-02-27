<?php
defined('ABSPATH') || exit;

if (defined('MWP_OBJECT_CACHE_DISABLED') && MWP_OBJECT_CACHE_DISABLED) {
	return;
}

if (defined('WP_CLI') && WP_CLI) {
	return;
}

$is_nocache = (bool)filter_input(INPUT_GET, 'nocache');
$is_sso = false;
$gd_command = filter_input(INPUT_GET, 'GD_COMMAND');
if (!empty($gd_command) && $gd_command == 'SSO_LOGIN') {
	$is_sso = true;
}
// Ignore nocache on SSO login
if ($is_nocache && !$is_sso) {
	return;
}

