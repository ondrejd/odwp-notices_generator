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
        global $wp_version;

        // Initialize locales
        $slug = 'odwp-notices_generator';
        load_plugin_textdomain( $slug, false, dirname( __FILE__ ) . '/languages' );

        /**
         * @var array Hold requirement errors
         */
        $errors = [];

        // Check PHP version
        if( ! empty( $requirements['php']['version'] ) ) {
            if( version_compare( phpversion(), $requirements['php']['version'], '<' ) ) {
                $errors[] = sprintf(
                        __( 'PHP nesplňuje nároky pluginu na minimální verzi (vyžadována nejméně <b>%s</b>)!', $slug ),
                        $requirements['php']['version']
                );
            }
        }

        // Check PHP extensions
        if( count( $requirements['php']['extensions'] ) > 0 ) {
            foreach( $requirements['php']['extensions'] as $req_ext ) {
                if( ! extension_loaded( $req_ext ) ) {
                    $errors[] = sprintf(
                            __( 'Je vyžadováno rozšíření PHP <b>%s</b>, to ale není nainstalováno!', $slug ),
                            $req_ext
                    );
                }
            }
        }

        // Check WP version
        if( ! empty( $requirements['wp']['version'] ) ) {
            if( version_compare( $wp_version, $requirements['wp']['version'], '<' ) ) {
                $errors[] = sprintf(
                        __( 'Plugin vyžaduje vyšší verzi platformy <b>WordPress</b> (minimálně <b>%s</b>)!', $slug ),
                        $requirements['wp']['version']
                );
            }
        }

        // Check WP plugins
        if( count( $requirements['wp']['plugins'] ) > 0 ) {
            $active_plugins = (array) get_option( 'active_plugins', [] );
            foreach( $requirements['wp']['plugins'] as $req_plugin ) {
                if( ! in_array( $req_plugin, $active_plugins ) ) {
                    $errors[] = sprintf(
                            __( 'Je vyžadován plugin <b>%s</b>, ten ale není nainstalován!', $slug ),
                            $req_plugin
                    );
                }
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
            if( $val != 'odwp-notices_generator/odwp-notices_generator.php' ) {
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
        // Enter minimum PHP version you needs.
        'version' => '5.6',
        // Enter extensions that your plugin needs
        'extensions' => [
            //'gd',
        ],
    ],
    'wp' => [
        // Enter minimum WP version you need
        'version' => '4.7',
        // Enter WP plugins that your plugin needs
        'plugins' => [
            //'woocommerce/woocommerce.php',
        ],
    ],
] );

// Check if requirements are met or not
if( count( $odwpng_errs ) > 0 ) {
    // Requirements are not met
    odwpng_deactivate_raw();

    // In administration print errors
    if( is_admin() ) {
        $err_head = '<b>Notices Generator</b>: ';
        foreach( $odwpng_errs as $err ) {
            printf( '<div class="error"><p>%s</p></div>', $err_head . $err );
        }
    }
} else {
    // Requirements are met so initialize the plugin.
    include( dirname( __FILE__ ) . '/src/Notices_Generator_Plugin.php' );
    Notices_Generator_Plugin::init();
}
