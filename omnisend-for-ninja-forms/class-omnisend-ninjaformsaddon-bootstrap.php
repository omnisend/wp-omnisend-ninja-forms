<?php
/**
 * Plugin Name: Omnisend for Ninja Forms Add-On
 * Description: A ninja forms add-on to sync contacts with Omnisend. In collaboration with Omnisend for WooCommerce plugin it enables better customer tracking
 * Version: 1.1.3
 * Author: Omnisend
 * Author URI: https://omnisend.com
 * Developer: Omnisend
 * Developer URI: https://omnisend.com
 * Text Domain: omnisend-for-ninja-forms-add-on
 * ------------------------------------------------------------------------
 * Copyright 2023 Omnisend
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package OmnisendNinjaFormsPlugin
 */

use Omnisend\NinjaFormsAddon\Actions\OmnisendAddOnAction;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const OMNISEND_NINJA_ADDON_NAME    = 'Omnisend for NINJA Forms Add-On';
const OMNISEND_NINJA_ADDON_VERSION = '1.1.3';

add_action( 'ninja_forms_register_actions', array( 'Omnisend_NinjaFormsAddOn_Bootstrap', 'register_actions' ), 10 );
spl_autoload_register( array( 'Omnisend_NinjaFormsAddOn_Bootstrap', 'autoloader' ) );
add_action( 'plugins_loaded', array( 'Omnisend_NinjaFormsAddOn_Bootstrap', 'check_plugin_requirements' ) );
add_action( 'admin_enqueue_scripts', array( 'Omnisend_NinjaFormsAddOn_Bootstrap', 'load_custom_wp_admin_style' ) );

/**
 * Class Omnisend_NinjaFormsAddOn_Bootstrap
 */
class Omnisend_NinjaFormsAddOn_Bootstrap {

	/**
	 * Register actions for the Omnisend Ninja Forms Add-On.
	 *
	 * @param array $actions The array of actions.
	 * @return array The modified array of actions.
	 */
	public static function register_actions( $actions ) {
		if ( ! class_exists( 'Omnisend\SDK\V1\Omnisend' ) ) {
			return array();
		}

		$path_to_snippet     = plugins_url( '/js/snippet.js', __FILE__ );
		$actions['omnisend'] = new OmnisendAddOnAction( $path_to_snippet );

		return $actions;
	}

	/**
	 * Autoloader function to load classes dynamically.
	 *
	 * @param string $class_name The name of the class to load.
	 */
	public static function autoloader( $class_name ) {
		$namespace = 'Omnisend\NinjaFormsAddon';

		if ( strpos( $class_name, $namespace ) !== 0 ) {
			return;
		}

		$class       = str_replace( $namespace . '\\', '', $class_name );
		$class_parts = explode( '\\', $class );
		$class_file  = 'class-' . strtolower( array_pop( $class_parts ) ) . '.php';

		$directory = plugin_dir_path( __FILE__ );
		$path      = $directory . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $class_parts ) . DIRECTORY_SEPARATOR . $class_file;

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}

	/**
	 * Check plugin requirements.
	 */
	public static function check_plugin_requirements() {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		$omnisend_plugin = 'omnisend/class-omnisend-core-bootstrap.php';

		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $omnisend_plugin ) || ! is_plugin_active( $omnisend_plugin ) ) {
			deactivate_plugins( 'omnisend-for-ninja-forms-add-on/class-omnisend-ninjaformsaddon-bootstrap.php' );
			add_action( 'admin_notices', array( 'Omnisend_NinjaFormsAddOn_Bootstrap', 'omnisend_woocommerce_notice' ) );

			return;
		}

		if ( ! class_exists( 'Omnisend\SDK\V1\Omnisend' ) ) {
			deactivate_plugins( 'omnisend-for-ninja-forms-add-on/class-omnisend-ninjaformsaddon-bootstrap.php' );
			add_action( 'admin_notices', array( 'Omnisend_NinjaFormsAddOn_Bootstrap', 'omnisend_woocommerce_not_up_to_date' ) );

			return;
		}

		if ( ! Omnisend\SDK\V1\Omnisend::is_connected() ) {
			deactivate_plugins( 'omnisend-for-ninja-forms-add-on/class-omnisend-ninjaformsaddon-bootstrap.php' );
			add_action( 'admin_notices', array( 'Omnisend_NinjaFormsAddOn_Bootstrap', 'omnisend_woocommerce_api_key_notice' ) );

			return;
		}

		$ninja_forms_plugin = 'ninja-forms/ninja-forms.php';
		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $ninja_forms_plugin ) || ! is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
			deactivate_plugins( 'omnisend-for-ninja-forms-add-on/class-omnisend-ninjaformsaddon-bootstrap.php' );
			add_action( 'admin_notices', array( 'Omnisend_NinjaFormsAddOn_Bootstrap', 'ninja_forms_notice' ) );
		}
	}

	/**
	 * Display a notice for the missing Omnisend WooCommerce API key.
	 */
	public static function omnisend_woocommerce_api_key_notice() {
		echo '<div class="error"><p>' . esc_html__( 'Your Email Marketing for WooCommerce by Omnisend is not configured properly. Please configure it firstly', 'ninja-forms' ) . '</p></div>';
	}

	/**
	 * Display a notice for the missing Omnisend WooCommerce plugin.
	 */
	public static function omnisend_woocommerce_notice() {
		echo '<div class="error"><p>' . esc_html__( 'Plugin Omnisend for Ninja Forms Add-On is deactivated. Please install and activate ', 'ninja-forms' ) . '<a href="https://wordpress.org/plugins/omnisend/">' . esc_html__( 'Email Marketing by Omnisend.', 'ninja-forms' ) . '</a></p></div>';
	}

	/**
	 * Display a notice for not up to date omnisend plugin.
	 */
	public static function omnisend_woocommerce_not_up_to_date() {
		echo '<div class="error"><p>' . esc_html__( 'Your Email Marketing by Omnisend is not up to date. Please update plugins ', 'ninja-forms' ) . '<a href="https://wordpress.org/plugins/omnisend/">' . esc_html__( 'Omnisend for Woocommerce plugin.', 'ninja-forms' ) . '</a></p></div>';
	}

	/**
	 * Display a notice for the missing Ninja Forms plugin.
	 */
	public static function ninja_forms_notice() {
		echo '<div class="error"><p>' . esc_html__( 'Plugin Omnisend for Ninja Forms Add-On is deactivated. Please install and activate Ninja forms plugin.', 'ninja-forms' ) . '</p></div>';
	}

	/**
	 * Loading styles in admin.
	 */
	public static function load_custom_wp_admin_style() {
		wp_register_style( 'omnisend-ninja-forms-addon', plugins_url( 'css/omnisend-ninjaforms-addon.css', __FILE__ ), array(), '1.0.4' );
		wp_enqueue_style( 'omnisend-ninja-forms-addon' );
	}
}
