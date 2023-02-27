<?php
/**
 * Plugin Name: WPEX Expert Banner
 * Plugin URI: https://godaddy.com/
 * Description: WPEX WordPress plugin for Expert Banner. Provides a link to expert services in the header of the editor screen. Intended to be used as a sub-plugin of System Plugin.
 * Version: 1.0.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: GoDaddy
 * Author URI: https://godaddy.com
 * Text Domain: expert-banner
 * Domain Path: /languages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * @package Expert_Banner
 */

defined('ABSPATH') || exit;

/**
 * Enqueue styles to handle expert banner styles.
 *
 * @action admin_enqueue_scripts
 *
 * @param string $hook
 */
function wpex_eb_enqueue_scripts()
{

    wp_enqueue_style(
        'wpaas-expert-banner',
        plugins_url("build/index.css", __FILE__),
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'wpaas-expert-banner',
        plugins_url("build/index.js", __FILE__),
        [],
        '1.0.0',
        true,
    );
}

add_action( 'admin_enqueue_scripts', 'wpex_eb_enqueue_scripts' );
