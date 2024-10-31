<?php
/**
 * Plugin Name: Search Logger
 * Description: Keep an eye on what your site visitors are looking for.
 * Plugin URI: https://codexpert.io/search-logger
 * Author: Codexpert
 * Author URI: https://codexpert.io
 * Version: 0.9
 * Text Domain: search-logger
 * Domain Path: /languages
 *
 * Search Logger is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 * 
 * Search Logger is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

namespace Codexpert\Search_Logger;
use Codexpert\Plugin\Survey;
use Codexpert\Plugin\Notice;
use Codexpert\Plugin\Deactivator;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for the plugin
 * @package Plugin
 * @author Codexpert <hi@codexpert.io>
 */
final class Plugin {
	
	public static $_instance;

	public function __construct() {
		$this->include();
		$this->define();
		$this->hook();
	}

	/**
	 * Includes files
	 */
	public function include() {
		require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
	}

	/**
	 * Define variables and constants
	 */
	public function define() {
		// constants
		define( 'SEARCH_LOGGER', __FILE__ );
		define( 'SEARCH_LOGGER_DIR', dirname( SEARCH_LOGGER ) );
		define( 'SEARCH_LOGGER_DEBUG', apply_filters( 'search-logger_debug', true ) );

		// plugin data
		$this->plugin				= get_plugin_data( SEARCH_LOGGER );
		$this->plugin['basename']	= plugin_basename( SEARCH_LOGGER );
		$this->plugin['file']		= SEARCH_LOGGER;
		$this->plugin['server']		= apply_filters( 'search-logger_server', 'https://codexpert.io/dashboard' );
		$this->plugin['min_php']	= '5.6';
		$this->plugin['min_wp']		= '4.0';

		global $search_logger;
		$search_logger = $this->plugin;
	}

	/**
	 * Hooks
	 */
	public function hook() {

		if( is_admin() ) :

			/**
			 * Admin facing hooks
			 *
			 * To add an action, use $admin->action()
			 * To apply a filter, use $admin->filter()
			 */
			$admin = new Admin( $this->plugin );
			$admin->activate( 'install' );
			$admin->deactivate( 'uninstall' );
			$admin->action( 'admin_enqueue_scripts', 'enqueue_scripts' );
			$admin->filter( "plugin_action_links_{$this->plugin['basename']}", 'action_links' );
			$admin->filter( 'plugin_row_meta', 'plugin_row_meta', 10, 2 );
			$admin->action( 'admin_footer_text', 'footer_text' );
			$admin->action( 'admin_head', 'delete_logs' );

			/**
			 * Settings related hooks
			 *
			 * To add an action, use $settings->action()
			 * To apply a filter, use $settings->filter()
			 */
			$settings = new Settings( $this->plugin );
			$settings->action( 'plugins_loaded', 'init_menu' );

			// Product related classes
			$survey			= new Survey( $this->plugin );
			$notice			= new Notice( $this->plugin );
			$deactivator	= new Deactivator( $this->plugin );

		else : // !is_admin() ?

			/**
			 * Front facing hooks
			 *
			 * To add an action, use $front->action()
			 * To apply a filter, use $front->filter()
			 */
			$front = new Front( $this->plugin );
			$front->action( 'wp_head', 'log_search' );

		endif;

		/**
		 * Common facing hooks
		 *
		 * To add an action, use $common->action()
		 * To apply a filter, use $common->filter()
		 */
		$common = new Common( $this->plugin );
		$common->action( 'plugins_loaded', 'i18n' );

		/**
		 * Cron facing hooks
		 *
		 * To add an action, use $cron->action()
		 * To apply a filter, use $cron->filter()
		 */
		$cron = new Cron( $this->plugin );
		$cron->activate( 'install' );
		$cron->deactivate( 'uninstall' );
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() { }

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() { }

	/**
	 * Instantiate the plugin
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

Plugin::instance();