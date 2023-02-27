<?php
return;

defined('ABSPATH') || exit;

define('RedisCachePro\Basename', basename(__FILE__));

foreach ([
			 defined('WP_REDIS_DIR') ? rtrim(WP_REDIS_DIR, '/') : null,
			 __DIR__ . '/redis-cache-pro',
			 __DIR__ . '/object-cache-pro',
		 ] as $path) {
	if (is_null($path)) {
		continue;
	}

	foreach (['redis-cache-pro.php', 'object-cache-pro.php'] as $file) {
		if (is_readable("{$path}/{$file}") && include_once "{$path}/{$file}") {
			return;
		}
	}
}

error_log('objectcache.critical: Failed to locate and load Object Cache Pro plugin');

if (defined('WP_DEBUG') && WP_DEBUG) {
	throw new RuntimeException('Failed to locate and load Object Cache Pro plugin');
}
