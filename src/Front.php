<?php
/**
 * All public facing functions
 */
namespace Codexpert\Search_Logger;
use Codexpert\Plugin\Base;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Front
 * @author Codexpert <hi@codexpert.io>
 */
class Front extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}

	public function log_search() {
		if( ! is_search() || Helper::get_option( 'search-logger_basic', 'enable_log' ) != 'on' ) return;

		$empty_log		= Helper::get_option( 'search-logger_basic', 'empty_log' ) == 'on';
		$log_user_data	= Helper::get_option( 'search-logger_basic', 'log_user_data' ) == 'on';

		$keyword = sanitize_text_field( $_GET['s'] );
		$results = [];

		if( ! $empty_log && $keyword == '' ) return;

		global $wpdb, $wp_query;

		foreach ( $wp_query->posts as $post ) {
			$results[] = $post->ID;
		}

		// insert search
		$wpdb->insert(
			"{$wpdb->prefix}searches",
			[
				'keyword'	=> $keyword,
				'results'	=> serialize( $results ),
				'count'		=> $wp_query->post_count,
				'timestamp'	=> time()
			]
		);

		$search_id = $wpdb->insert_id;

		// insert search meta
		$metas = [
			'ip'	=> sanitize_text_field( $_SERVER['REMOTE_ADDR'] )
		];

		if( $log_user_data && is_user_logged_in() ) {
			$metas['user_id'] = get_current_user_id();
		}

		foreach ( $metas as $meta_key => $meta_value ) { // @TODO: insert with a single MySQL query
			$wpdb->insert(
				"{$wpdb->prefix}searchmeta",
				[
					'search_id'	=> $search_id,
					'meta_key'	=> $meta_key,
					'meta_value'=> $meta_value
				]
			);
		}
	}
}