<?php
/**
 * Plugin Name: Goldnat for Yoast SEO
 * Plugin URI: https://goldnat.ai/plugins/goldt-webmcp-yoast
 * Description: Yoast SEO integration for Goldnat — exposes SEO metadata, focus keywords, readability/SEO scores, robots directives, social meta and cornerstone content as AI tools via the Goldnat WebMCP Bridge.
 * Version: 1.0.0
 * Author: chgold
 * Author URI: https://goldnat.ai/
 * License: Proprietary
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: goldt-webmcp-bridge, wordpress-seo
 * Text Domain: goldt-webmcp-yoast
 * @package GoldtWebMCP\Yoast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GOLDTWMCP_YOAST_VERSION', '1.0.0' );
define( 'GOLDTWMCP_YOAST_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Verify the free Bridge core + Yoast SEO are present.
 *
 * @return bool
 */
function goldtwmcp_yoast_check_dependencies() {
	if ( ! defined( 'GOLDTWMCP_VERSION' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>Goldnat for Yoast SEO:</strong> Requires the free Goldnat WebMCP Bridge plugin.</p></div>';
		} );
		return false;
	}
	if ( ! defined( 'WPSEO_VERSION' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>Goldnat for Yoast SEO:</strong> Requires the Yoast SEO plugin (wordpress-seo).</p></div>';
		} );
		return false;
	}
	return true;
}

// Register the Yoast module with the bridge (bridge fires the hook at 9999).
add_action( 'plugins_loaded', function () {
	if ( ! goldtwmcp_yoast_check_dependencies() ) {
		return;
	}
	add_action( 'goldtwmcp_register_modules', 'goldtwmcp_yoast_register_modules' );
}, 30 );

/**
 * Instantiate + register the Yoast module.
 *
 * @param \GoldtWebMCP\Core\GoldtWebMCP $goldtwmcp Bridge instance.
 * @return void
 */
function goldtwmcp_yoast_register_modules( $goldtwmcp ) {
	require_once GOLDTWMCP_YOAST_PATH . 'includes/modules/class-yoast-module.php';
	$manifest = $goldtwmcp->get_manifest_instance();
	$module   = new \GoldtWebMCP\Modules\Yoast_Module( $manifest );
	$goldtwmcp->register_external_module( 'yoast', $module );
}
