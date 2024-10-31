<?php
/**
 * All admin facing functions
 */
namespace Codexpert\Search_Logger;
use Codexpert\Plugin\Base;
use Codexpert\Plugin\Wizard;
use Codexpert\Plugin\Metabox;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Admin
 * @author Codexpert <hi@codexpert.io>
 */
class Admin extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->server	= $this->plugin['server'];
		$this->version	= $this->plugin['Version'];
	}

	/**
	 * Installer. Runs once when the plugin in activated.
	 *
	 * @since 1.0
	 */
	public function install() {

		if( ! get_option( 'search-logger_version' ) ){
			update_option( 'search-logger_version', $this->version );
		}
		
		if( ! get_option( 'search-logger_install_time' ) ){
			update_option( 'search-logger_install_time', time() );
		}

		/**
		 * Create database tables
		 */
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// `seaches` table
		dbDelta( "CREATE TABLE `{$wpdb->prefix}searches` (
			id bigint NOT NULL AUTO_INCREMENT,
			keyword varchar(255) NOT NULL DEFAULT '',
			results longtext NOT NULL,
			count int(10) NOT NULL,
			timestamp int(10) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;" );

		// `searchmeta` table
		dbDelta( "CREATE TABLE `{$wpdb->prefix}searchmeta` (
			meta_id bigint NOT NULL AUTO_INCREMENT,
			search_id bigint NOT NULL,
			meta_key varchar(255) NOT NULL,
			meta_value longtext NOT NULL,
			PRIMARY KEY (meta_id)
		) $charset_collate;" );
	}
	
	/**
	 * Enqueue JavaScripts and stylesheets
	 */
	public function enqueue_scripts() {
		$min = defined( 'SEARCH_LOGGER_DEBUG' ) && SEARCH_LOGGER_DEBUG ? '' : '.min';
		
		wp_enqueue_style( $this->slug, plugins_url( "/assets/css/admin{$min}.css", SEARCH_LOGGER ), '', $this->version, 'all' );

		wp_enqueue_script( $this->slug, plugins_url( "/assets/js/admin{$min}.js", SEARCH_LOGGER ), [ 'jquery' ], $this->version, true );
	}

	public function action_links( $links ) {
		$this->admin_url = admin_url( 'tools.php' );

		$new_links = [
			'settings'	=> sprintf( '<a href="%1$s">' . __( 'Search Logs', 'search-logger' ) . '</a>', add_query_arg( 'page', $this->slug, $this->admin_url ) )
		];
		
		return array_merge( $new_links, $links );
	}

	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		
		if ( $this->plugin['basename'] === $plugin_file ) {
			$plugin_meta['help'] = '<a href="https://help.codexpert.io/" target="_blank" class="cx-help">' . __( 'Help', 'search-logger' ) . '</a>';
		}

		return $plugin_meta;
	}

	public function footer_text( $text ) {
		if( get_current_screen()->base != 'tools_page_search-logger' ) return $text;

		return sprintf( __( 'If you like <strong>%1$s</strong>, please <a href="%2$s" target="_blank">leave us a %3$s rating</a> on WordPress.org! It\'d motivate and inspire us to make the plugin even better!', 'search-logger' ), $this->name, "https://wordpress.org/support/plugin/{$this->slug}/reviews/?filter=5#new-post", '⭐⭐⭐⭐⭐' );
	}

	public function delete_logs() {
		if(
			isset( $_GET['page'] ) && $_GET['page'] == 'search-logger'
			&& isset( $_POST['action'] ) && $_POST['action'] == 'delete'
		) {
			global $wpdb;
			$ids = implode( ',', array_map( 'sanitize_text_field', $_POST['ids'] ) );
			$wpdb->query( "DELETE FROM `{$wpdb->prefix}searches` WHERE id IN($ids)" );
			$wpdb->query( "DELETE FROM `{$wpdb->prefix}searchmeta` WHERE search_id IN($ids)" );
		}
	}
}