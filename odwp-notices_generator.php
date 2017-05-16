<?php
/**
 * Plugin Name: Notices Generator
 * Plugin URI: https://github.com/ondrejd/odwp-notices_generator
 * Description: Plugin that allows generate funeral (or others) notices to the users.
 * Version: 1.0.0
 * Author: Ondřej Doněk
 * Author URI:
 * License: GPLv3
 * Requires at least: 4.7
 * Tested up to: 4.7.4
 *
 * Text Domain: odwp-notices_generator
 * Domain Path: /languages/
 *
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-notices_generator for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-notices_generator
 */

/**
 * This file is just a bootstrap. It checks if requirements of plugins are met
 * and accordingly either initializes the plugin or halts the process.
 *
 * Requirements can be specified for PHP and the WordPress self - version
 * for both, required extensions for PHP and requireds plugins for WP.
 *
 * If you are using copy of original file in your plugin you shoud change
 * prefix "odwpng" and name "odwpng-notices_generator" to your own values.
 *
 * For setting the requirements go down to line 67 and define array that
 * is used as a parameter for `odwpng_check_requirements` function.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! function_exists( 'odwpng_check_requirements' ) ) :
    /**
     * Checks requirements of our plugin.
     * @param array $requirements
     * @return array
     */
    function odwpng_check_requirements( array $requirements ) {
        $errors = [];

        // Check PHP version
        //...

        // Check PHP extensions
        //...

        // Check WP version
        //...

        // Check WP plugins
        $active_plugins = (array) get_option( 'active_plugins', [] );
        foreach( $requirements['wp']['plugins'] as $req_plugin ) {
            if( ! in_array( $req_plugin, $active_plugins ) ) {
                // XXX Error...
            }
        }

        return $errors;
    }
endif;

if( ! function_exists( 'odwpng_deactivate_raw' ) ) :
    /**
     * Deactivate plugin by the raw way.
     * @return void
     */
    function odwpng_deactivate_raw() {
        $active_plugins = get_option( 'active_plugins' );
        $out = [];
        foreach( $active_plugins as $key => $val ) {
            if( $val != sprintf( "%$1s/%$1s.php", 'odwp-notices_generator' ) ) {
                $out[$key] = $val;
            }
        }
        update_option( 'active_plugins', $out );
    }
endif;

/**
 * Errors from the requirements check
 * @var array
 */
$odwpng_errs = odwpng_check_requirements( [
    'php' => [
        'version' => '5.6',
        'extensions' => [],
    ],
    'wp' => [
        'version' => '4.7',
        'plugins' => [
            //'woocommerce/woocommerce.php',
        ],
    ],
] );

if( count( $odwpng_errs ) > 0 ) {
    // Requirements are not met
    odwpng_deactivate_raw();

    if( is_admin() ) {
        foreach( $odwpng_errs as $err ) {
            printf( '<div class="%s"><p>%s</p></div>', $err['type'], $err['text'] );
        }
    }
} else {
    // Requirements are met so initialize the plugin.
    include( dirname( __FILE__ ) . '/src/Notices_Generator_Plugin.php' );
    Notices_Generator_Plugin::init();
}
