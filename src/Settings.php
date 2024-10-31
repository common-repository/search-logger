<?php
/**
 * All settings related functions
 */
namespace Codexpert\Search_Logger;
use Codexpert\Plugin\Base;

/**
 * @package Plugin
 * @subpackage Settings
 * @author Codexpert <hi@codexpert.io>
 */
class Settings extends Base {

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
	
	public function init_menu() {
		
		$settings = [
			'id'            => $this->slug,
			'label'         => $this->name,
			'title'         => $this->name,
			'header'        => $this->name,
			'parent'		=> 'tools.php',
			'icon'			=> 'dashicons-search',
			'sections'      => [
				'search-logger_basic'	=> [
					'id'        => 'search-logger_basic',
					'label'     => __( 'Settings', 'search-logger' ),
					'icon'      => 'dashicons-admin-tools',
					'color'		=> '#4c3f93',
					'sticky'	=> false,
					'fields'    => [
						'enable_log' => [
							'id'        => 'enable_log',
							'label'     => __( 'Enable Log', 'search-logger' ),
							'type'      => 'switch',
							'desc'      => __( 'Enable this to keep log of searches.', 'search-logger' ),
						],
						'empty_log' => [
							'id'        => 'empty_log',
							'label'     => __( 'Log Empty Search', 'search-logger' ),
							'type'      => 'switch',
							'desc'      => __( 'Should it log a search even if no search term/keyword is given?', 'search-logger' ),
							'condition'	=> [
								'key'		=> 'enable_log',
								'value'		=> 'on',
								'compare'	=> 'checked'
							]
						],
						'log_user_data' => [
							'id'        => 'log_user_data',
							'label'     => __( 'Store User Data', 'search-logger' ),
							'type'      => 'switch',
							'desc'      => __( 'Enable this to store the user\'s data if they are logged in.', 'search-logger' ),
							'condition'	=> [
								'key'		=> 'enable_log',
								'value'		=> 'on',
								'compare'	=> 'checked'
							]
						],
					]
				],
				'search-logger_table' => [
					'id'        => 'search-logger_table',
					'label'     => __( 'Logs', 'search-logger' ),
					'icon'      => 'dashicons-editor-table',
					'color'		=> '#e35b1a',
					'hide_form'	=> true,
					'template'  => SEARCH_LOGGER_DIR . '/views/settings/table.php',
				],
			],
		];

		new \Codexpert\Plugin\Settings( $settings );
	}
}