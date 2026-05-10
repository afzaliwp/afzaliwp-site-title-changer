<?php
/**
 * Plugin Name: AfzaliWP Title Changer
 * Plugin URI: https://afzaliwp.com
 * Description: Change your WordPress site title from a dedicated front-end page with full authentication and changelog.
 * Version: 1.0.0
 * Author: Mohammad Afzali
 * Author URI: https://afzaliwp.com
 * Text Domain: afzaliwp-tc
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires at least PHP: 7.4
 */

namespace AfzaliWP;

use AfzaliWP\TitleChanger\Includes\Activation;
use AfzaliWP\TitleChanger\Includes\Deactivation;
use AfzaliWP\TitleChanger\Includes\Page;
use AfzaliWP\TitleChanger\Includes\Ajax;
use Exception;

defined( 'ABSPATH' ) || die();

require 'functions.php';

final class TitleChanger {

	private static $instances = [];

	protected function __construct() {
		register_activation_hook( __FILE__, [ $this, 'activation' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivation' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	protected function __clone() {
	}

	public function __wakeup() {
		throw new Exception( "Cannot unserialize a singleton." );
	}

	public static function get_instance() {
		$cls = TitleChanger::class;

		if ( ! isset( self::$instances[ $cls ] ) ) {
			self::$instances[ $cls ] = new TitleChanger();
		}

		return self::$instances[ $cls ];
	}

	public function init() {
		spl_autoload_register( 'afzaliwp_tc_autoload' );

		$this->define_constants();

		add_action( 'wp_enqueue_scripts', [ $this, 'register_styles_and_scripts' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );

		$this->load();
		$this->load_plugin_textdomain();
	}

	public function activation() {
		spl_autoload_register( 'afzaliwp_tc_autoload' );
		$this->define_constants();
		new Activation();
	}

	public function deactivation() {
		spl_autoload_register( 'afzaliwp_tc_autoload' );
		$this->define_constants();
		new Deactivation();
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'afzaliwp-tc',
			false,
			basename( AFZALIWP_TC_DIR ) . '/languages/'
		);
	}

	public function register_styles_and_scripts() {
		$page_id = get_option( 'afzaliwp_tc_page_id' );

		if ( ! $page_id || ! is_page( $page_id ) ) {
			return;
		}

		wp_enqueue_style(
			'afzaliwp-tc-style',
			AFZALIWP_TC_URL . 'assets/dist/frontend.min.css',
			[],
			AFZALIWP_TC_ASSETS_VERSION
		);

		wp_enqueue_script(
			'afzaliwp-tc-script',
			AFZALIWP_TC_URL . 'assets/dist/frontend.min.js',
			[ 'jquery' ],
			AFZALIWP_TC_ASSETS_VERSION,
			true
		);

		wp_localize_script(
			'afzaliwp-tc-script',
			'afzaliwp_tc_params',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'afzaliwp_tc_nonce' ),
			]
		);
	}

	public function plugin_action_links( $links ) {
		$page_id = get_option( 'afzaliwp_tc_page_id' );

		if ( $page_id && get_post_status( $page_id ) === 'publish' ) {
			$url     = get_permalink( $page_id );
			$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Title Changer Page', 'afzaliwp-tc' ) . '</a>';
		}

		return $links;
	}

	public function define_constants() {
		if ( defined( 'AFZALIWP_TC_DIR' ) ) {
			return;
		}

		define( 'AFZALIWP_TC_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'AFZALIWP_TC_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'AFZALIWP_TC_INC_DIR', trailingslashit( AFZALIWP_TC_DIR . 'includes' ) );

		if ( str_contains( get_bloginfo( 'wpurl' ), 'local' ) ) {
			define( 'AFZALIWP_TC_ASSETS_VERSION', time() );
		} else {
			define( 'AFZALIWP_TC_ASSETS_VERSION', '1.0.0' );
		}
	}

	private function load() {
		new Page();
		new Ajax();
	}
}

TitleChanger::get_instance();
