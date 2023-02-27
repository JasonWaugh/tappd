<?php

namespace WPaaS;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Cache_V2 {
	/**
	 * Arbitrary number after some analyse, as we make x2 requests (HTTP and HTTPS)
	 *
	 * @var int
	 */
	const MAX_PURGE_URLS = 20;

	/**
	 * Maximum number of ban requests per 5 minute
	 *
	 * @var int
	 */
	const MAX_BAN_LIMIT = 8;
	const MAX_BAN_LIMIT_TTL = 300;

	/**
	 * How much ban options can be done from non-logged in users
	 */
	CONST MAX_OPTION_BAN_LIMIT = 1;
	CONST MAX_OPTION_BAN_TTL = 3600;

	/**
	 * @var string
	 */
	const CACHE_BAN_KEY = 'wpaas_cache_cnt';
	/**
	 * @var string
	 */
	const CACHE_BAN_OPTIONS_KEY = 'wpaas_cache_options_cnt';

	/**
	 * Required user capability.
	 *
	 * @var string
	 */
	public static $cap = 'activate_plugins';

	/**
	 * Array of URLs to be purged.
	 *
	 * @var array
	 */
	public static $purge_urls = [];

	/**
	 * Curl host to resolve to VIP
	 *
	 * @var array
	 */
	public static $curlResolvePairs = [];

	/**
	 * Instance of the API.
	 *
	 * @var API_Interface
	 */
	private static $api;

	/**
	 * Class constructor.
	 *
	 * @param API_Interface $api
	 */
	public function __construct(API_Interface $api) {

		self::$api = $api;
		$GLOBALS['wpaas_cache_class'] = $this;

		/**
		 * Filter the user cap required to flush cache.
		 *
		 * @since 2.0.0
		 *
		 * @var string
		 */
		self::$cap = (string) apply_filters( 'wpaas_flush_cache_cap', self::$cap );

		add_action( 'init', [ $this, 'init' ], -PHP_INT_MAX );
		add_action( 'admin_enqueue_scripts', [ $this, 'flush_cdn_polling_script' ], -PHP_INT_MAX );
		// General Settings/Theme/Widgets customization
		add_action( 'update_option', [ $this, 'update_option' ], PHP_INT_MAX, 1 );
		add_action( 'customize_save', [ $this, 'do_ban' ], PHP_INT_MAX, 0 );
		// Theme/Plugin change
		add_action( 'switch_theme', [ $this, 'do_ban' ], PHP_INT_MAX, 0 );
		add_action( 'activated_plugin', [ $this, 'do_ban' ], PHP_INT_MAX, 0 );
		add_action( 'deactivated_plugin', [ $this, 'do_ban' ], PHP_INT_MAX, 0 );
		// Core upgrade
		add_action( '_core_updated_successfully', [ $this, 'do_ban' ], PHP_INT_MAX, 0 );
		add_action( 'upgrader_process_complete', [ $this, 'do_ban' ], PHP_INT_MAX, 0 );
		// Navigation
		add_action( 'wp_update_nav_menu', [ $this, 'do_ban' ], PHP_INT_MAX, 0 );
		add_action( 'wp_delete_nav_menu', [ $this, 'do_ban' ], PHP_INT_MAX, 0 );
		add_action( 'wp_create_nav_menu', [ $this, 'do_ban' ], PHP_INT_MAX, 0 );
		// General WP cache clean
		add_action( 'clean_post_cache', [ $this, 'do_purge' ], PHP_INT_MAX, 2 );
		add_action( 'clean_comment_cache', [ $this, 'do_purge' ], PHP_INT_MAX );
		// Taxonomy
		add_action( 'created_category', [ $this, 'do_ban' ], PHP_INT_MAX, 0);
		add_action( 'created_post_tag', [ $this, 'do_ban' ], PHP_INT_MAX, 0);
		add_action( 'edited_category', [ $this, 'do_ban' ], PHP_INT_MAX, 0);
		add_action( 'edited_post_tag', [ $this, 'do_ban' ], PHP_INT_MAX, 0);
		add_action( 'delete_category', [ $this, 'do_ban' ], PHP_INT_MAX, 0);
		add_action( 'delete_post_tag', [ $this, 'do_ban' ], PHP_INT_MAX, 0);

		// GoDadddy Launch Plugin
		add_action( 'gdl_splash_page_set', [ $this, 'do_ban' ], PHP_INT_MAX, 0 );

		// Skip cache
		add_filter( 'script_loader_src', [ $this, 'nocache' ] );
		add_filter( 'style_loader_src', [ $this, 'nocache' ] );

		// Curl Resolve binding
		add_action( 'http_api_curl', [$this, 'curl_resolve'], PHP_INT_MAX, 3);

	}

	public function curl_resolve($handle, $parsed_args, $url){
		curl_setopt($handle, CURLOPT_RESOLVE , self::$curlResolvePairs);
	}

	/**
	 * Make a non-blocking request to Varnish.
	 *
	 * @param string $method
	 * @param string $url (optional)
	 */
	private function request( $method, $url = null ) {

		$url  = empty( $url ) ? home_url() : $url;
		$host = parse_url( $url, PHP_URL_HOST );

		$urlHttp      = set_url_scheme($url, 'http');
		$urlHttps     = set_url_scheme($url, 'https');
		$httpResolve  = $host.":80:".Plugin::vip();
		$httpsResolve = $host.":443:".Plugin::vip();

		if (!in_array($httpResolve, self::$curlResolvePairs)) {
			self::$curlResolvePairs[] = $httpResolve;
		}
		if (!in_array($httpsResolve, self::$curlResolvePairs)) {
			self::$curlResolvePairs[] = $httpsResolve;
		}

		wp_cache_flush();

		// This will force persistent APCu cache to flush across servers.
		update_option( 'gd_system_last_cache_flush', time() );

		$this->executeRequest($method, $host, $urlHttp);
		$this->executeRequest($method, $host, $urlHttps);

	}

	private function executeRequest($method, $host, $url)
	{
		wp_remote_request(
			esc_url_raw($url),
			[
				'method'   => $method,
				'blocking' => false,
				'headers'  => [
					'Host' => $host,
				],
			]
		);
	}

	/**
	 * Initialize script.
	 *
	 * @action init
	 */
	public function init() {

		$action = filter_input( INPUT_GET, 'wpaas_action' );
		$nonce  = filter_input( INPUT_GET, 'wpaas_nonce' );

		if (
			! current_user_can( self::$cap )
			||
			'flush_cache' !== $action
			||
			false === wp_verify_nonce( $nonce, 'wpaas_flush_cache' )
		) {

			return;

		}
		if ( ! $this->is_token_under_limit( self::CACHE_BAN_KEY, self::MAX_BAN_LIMIT, self::MAX_BAN_LIMIT_TTL ) ) {
			Admin\Growl::add( __( 'You have exceeded the maximum number of cache flushes. Please wait 5 minutes', 'gd-system-plugin' ) );
			wp_safe_redirect(
				esc_url_raw(
					remove_query_arg(
						[
							'GD_COMMAND', // Backwards compat
							'wpaas_action',
							'wpaas_nonce',
						]
					)
				)
			);
			exit;
		}
		$this->do_ban();

		$this->flush_transients();
		$this->flush_ob();

		$cdn_full_page = defined('GD_CDN_FULLPAGE') ? GD_CDN_FULLPAGE : false;

		if ($cdn_full_page ) {
			Admin\Growl::add( __( 'Clear cache in progress', 'gd-system-plugin' ) );
		} else {
			Admin\Growl::add( __( 'Cache cleared', 'gd-system-plugin' ) );
		}

		wp_safe_redirect(
			esc_url_raw(
				remove_query_arg(
					[
						'GD_COMMAND', // Backwards compat
						'wpaas_action',
						'wpaas_nonce',
					]
				)
			)
		);

		exit;

	}

	/**
	 * Flush cache on shutdown when certain options are updated.
	 *
	 * @action update_option
	 *
	 * @param string $option
	 */
	public function update_option( $option ) {

		$options = [
			'avatar_default',
			'blogdescription',
			'blogname',
			'category_base',
			'close_comments_days_old',
			'close_comments_for_old_posts',
			'comment_order',
			'comment_registration',
			'comments_per_page',
			'date_format',
			'default_comments_page',
			'gmt_offset',
			'page_comments',
			'page_for_posts',
			'page_on_front',
			'permalink_structure',
			'rewrite_rules',
			'posts_per_page',
			'require_name_email',
			'show_avatars',
			'sidebars_widgets',
			'start_of_week',
			'tag_base',
			'thread_comments',
			'thread_comments_depth',
			'time_format',
			'timezone_string',
			'WPLANG',
			"siteurl",
			"home",
		];

		if (
			in_array( $option, $options, true )
			||
			0 === strpos( $option, 'widget_' )
			||
			0 === strpos( $option, 'theme_mods_' )
		) {
			$limit = self::MAX_OPTION_BAN_LIMIT;

			if ( get_current_user_id() > 0 ){
				$limit = $limit * 3;
			}
			if ( $this->is_token_under_limit(self::CACHE_BAN_OPTIONS_KEY, $limit, self::MAX_OPTION_BAN_TTL) ) {
				$this->increment_token(self::CACHE_BAN_OPTIONS_KEY, $limit, self::MAX_OPTION_BAN_TTL);
				$this->do_ban();
			}
		}

	}

	/**
	 * Set to ban cache on shutdown.
	 */
	public function do_ban() {

		if ( $this->has_ban() ) {

			return;

		}

		remove_action( 'shutdown', [ $GLOBALS['wpaas_cache_class'], 'purge' ], PHP_INT_MAX );

		add_action( 'shutdown', [ $GLOBALS['wpaas_cache_class'], 'ban' ], PHP_INT_MAX );

	}

	/**
	 * Set purge URLs and set to purge cache on shutdown.
	 *
	 * @param int      $id
	 * @param \WP_Post $post (optional)
	 */
	public function do_purge( $id, $post = null ) {

		if ( $this->has_ban() ) {

			return;

		}

		if ( ! is_a( $post, 'WP_Post' ) ) {

			// Assume anything that isn't a post is a comment
			$comment = get_comment( $id );

			if ( ! is_a( $comment, 'WP_Comment' ) ) {

				return;

			}

			$post = get_post( $comment->comment_post_ID );

		}

		if ( wp_is_post_revision( $post ) ) {

			return;

		}

		/**
		 * Purge all URLs where a post might appear
		 */
		self::$purge_urls[] = trailingslashit( home_url() );
		self::$purge_urls[] = get_permalink( $post->ID );
		$post_archive_link  = get_post_type_archive_link( $post->post_type );
		if ( $post_archive_link != get_home_url() ) { // omit archive link that is same as homepage
			self::$purge_urls[] = $post_archive_link;
		}
		self::$purge_urls[] = get_post_type_archive_feed_link( $post->post_type );
		self::$purge_urls[] = get_author_posts_url( (int) $post->post_author );

		// Taxonomy-related URLs
		foreach ( get_post_taxonomies( $post ) as $tax ) {

			$post_terms = wp_get_post_terms( $post->ID, $tax );

			if ( is_wp_error( $post_terms ) ) {

				continue;

			}

			foreach ( $post_terms as $term ) {

				self::$purge_urls[] = get_term_link( $term );
				self::$purge_urls[] = get_term_feed_link( $term->term_id, $term->taxonomy );

			}

		}

		foreach ( self::$purge_urls as $key => $url ) {

			// Archive page might return false
			if ( ! $url || is_wp_error( $url ) ) {

				unset( self::$purge_urls[ $key ] );

			}

		}

		self::$purge_urls = array_values( array_unique( self::$purge_urls ) );

		if ($this->should_switch_to_ban()) {
			$this->do_ban();

			return;
		}

		if ( ! $this->has_purge() ) {

			add_action( 'shutdown', [ $GLOBALS['wpaas_cache_class'], 'purge' ], PHP_INT_MAX );

		}

	}

	/**
	 * Delete all transient data from the options table
	 *
	 * WordPress only deletes expired transients when something tries
	 * to call that transient key again. This means over time there could
	 * be many thousands of transient option rows polluting the database,
	 * which can result in noticable performance impact.
	 *
	 * This method should be called when the customer is explicitly
	 * clearing their site's cache. Since transients are a form of cache,
	 * we will flush them all away regardless of TTL status.
	 *
	 * @see HOSTAPPS-3157/WPDEV-708
	 *
	 * @return int|false Number of rows affected/selected or false on error.
	 */
	public function flush_transients() {

		global $wpdb;

		return $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '%_transient_%';" );

	}

	/**
	 * Clear OBP cache even when nocache=1 is used.
	 *
	 * @see VOICEIT-9348
	 *
	 * @return void
	 */
	public function flush_ob() {
		if (isset($GLOBALS['ObjectCachePro'])) {
			$GLOBALS['ObjectCachePro']->flush();
		}
	}

	/**
	 * Return a nonced flush cache URL.
	 *
	 * @return string
	 */
	public static function get_flush_url() {

		return esc_url(
			add_query_arg(
				[
					'wpaas_action' => 'flush_cache',
					'wpaas_nonce'  => wp_create_nonce( 'wpaas_flush_cache' ),
				]
			)
		);

	}

	/**
	 * Perform CDN flush
	 *
	 * @return void
	 */
	public function flush_cdn() {
		if ( defined('GD_CDN_FULLPAGE') &&  true === GD_CDN_FULLPAGE ) {
			$invalidation_id = self::$api->flush_cdn();
			if ( $invalidation_id ) {
				update_option( 'gd_system_polling_invalidation_id', $invalidation_id );
			}
			return $invalidation_id;
		}
	}

	public function flush_cdn_polling_script( ) {
		if ( get_option('gd_system_polling_invalidation_id') ) {
			wp_enqueue_script( 'wpaas-flush-cdn-polling', Plugin::assets_url( 'js/flush-cdn-status-polling.js' ), [ 'jquery' ] );
			wp_localize_script( 'wpaas-flush-cdn-polling' , 'wpaas_flush_cdn_polling_object', [
				'ajaxUrl' => esc_url_raw( rest_url() ) . 'wpaas/v1/flush-cache/status',
				'nonce' => wp_create_nonce( 'wp_rest' )
			]);
		}
	}
	/**
	 * Send request to web tier to flush OBP cache on CLI action
	 *
	 * @see MWPPLAT-3388
	 * @return void
	 */
	public function web_flush_cache() {
		$domain = defined( 'GD_TEMP_DOMAIN' ) ? GD_TEMP_DOMAIN : null;

		if ( !$domain ) {
			return;
		}

		$api_url = sprintf('https://%s/wp-json/wpaas/v1/flush-cache', $domain);
		$data    = Plugin::sign_http_request(wp_json_encode( [] ));
		$headers = [];
		foreach ($data as $key => $item) {
			$headers[str_replace('wp-', '', $key)] = $item;
		}

		wp_remote_request(
			esc_url_raw(  $api_url ),
			[
				'method'   => 'POST',
				'blocking' => true,
				'headers'  => array_merge( [
					'Accept'         => 'application/json',
					'Content-Type'   => 'application/json',
				], $headers ),
			]
		);
	}

	/**
	 * Check if a BAN request is already set to fire on shutdown.
	 *
	 * @return bool
	 */
	public function has_ban() {

		return has_action( 'shutdown', [ $GLOBALS['wpaas_cache_class'], 'ban' ] );

	}

	/**
	 * Check if a PURGE request is already set to fire on shutdown.
	 *
	 * @return bool
	 */
	public function has_purge() {

		return has_action( 'shutdown', [ $GLOBALS['wpaas_cache_class'], 'purge' ] );

	}

	/**
	 * Ban all cache (async).
	 *
	 * @return bool
	 */
	public function ban() {

		if ( 'shutdown' !== current_action() ) {

			return false;

		}

		if ( ! $this->is_token_under_limit( self::CACHE_BAN_KEY, self::MAX_BAN_LIMIT, self::MAX_BAN_LIMIT_TTL ) ) {
			return false;
		}
		$this->increment_token( self::CACHE_BAN_KEY, self::MAX_BAN_LIMIT, self::MAX_BAN_LIMIT_TTL );

		$this->request( 'BAN' );
		if ( Plugin::is_wp_cli() ) {
			$this->web_flush_cache();
		}

		$this->flush_cdn();

		/**
		 * Fires after all site cache has been banned.
		 *
		 * @since 2.0.1
		 */
		do_action( 'wpaas_cache_banned' );

		return true;

	}

	/**
	 * Purge the Varnish cache selectively (async).
	 *
	 * @param  array $urls (optional)
	 *
	 * @return bool
	 */
	public function purge( $urls = [] ) {

		if ( 'shutdown' !== current_action() ) {

			return false;

		}

		$urls = ( $urls ) ? $urls : self::$purge_urls;

		if ( ! $urls ) {

			return false;

		}

		$urls = array_unique( $urls );

		foreach ( $urls as $url ) {

			$this->request( 'PURGE', $url );

		}

		/**
		 * Fires after cache has been purged on specific URLs.
		 *
		 * @since 2.0.1
		 *
		 * @param array $urls
		 */
		do_action( 'wpaas_cache_purged', $urls );

		return true;

	}

	/**
	 * Propogate nocache call to scripts and styles.
	 *
	 * When the `nocache` query arg is being used in the page
	 * request we need to ensure that any scripts and styles
	 * from this domain being called also use it.
	 *
	 * @filter script_loader_src
	 * @filter style_loader_src
	 *
	 * @param  string $src
	 *
	 * @return string
	 */
	public function nocache( $src ) {

		$is_external = ( false === stripos( $src, Plugin::domain() ) );
		$is_nocache  = ( false !== stripos( filter_input( INPUT_SERVER, 'QUERY_STRING' ), 'nocache' ) );

		if ( ! $is_external && $is_nocache ) {

			$src = add_query_arg( 'nocache', 1, $src );

		}

		return $src;

	}

	/**
	 *
	 * @return bool
	 */
	private function should_switch_to_ban() {
                $cdn_full_page = defined('GD_CDN_FULLPAGE') ? GD_CDN_FULLPAGE : false;

		if ( $cdn_full_page || count(self::$purge_urls) > self::MAX_PURGE_URLS ) {
			return true;
		}

		return false;
	}

	/**
	 * Return true if specific token key is under limit and actions is allowed
	 *
	 * @param string $token
	 * @param int $limit
	 * @param int $ttl
	 * @return bool
	 */
	private function is_token_under_limit( $token, $limit, $ttl ) {
		$cutoff_time = time() - $ttl;

		$value = get_option( $token, false );
		if ( $value === false ) {
			return true;
		}
		if ( ! is_array( $value ) ) {
			$value = [];
		}
		$counter = 0;
		foreach ( $value as $v ) {
			if ( $v > $cutoff_time ) {
				$counter ++;
			}
		}


		return $counter < $limit;
	}

	/**
	 * Increment specific token counter
	 *
	 * @param string $token
	 * @param int $limit
	 * @param int $ttl
	 * @return void
	 */
	private function increment_token( $token, $limit, $ttl ) {
		$insert       = false;
		$current_time = time();
		$cutoff_time  = time() - $ttl;

		$value = get_option( $token, false );
		if ( $value === false ) {
			$value  = [];
			$insert = true;
		}
		if ( is_array( $value ) === false ) {
			$value = [];
		}

		$new_array = [];
		foreach ( $value as $v ) {
			if ( $v > $cutoff_time ) {
				$new_array[] = $v;
			}
		}
		if ( count( $new_array ) < $limit ) {
			$new_array[] = $current_time;
		}

		$this->upsert( $token, $new_array, $insert );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param bool $insert
	 * @return void
	 */
	private function upsert( $key, $value, $insert ) {
		if ( $insert ) {
			add_option( $key, $value );
		} else {
			update_option( $key, $value );
		}
	}
}
