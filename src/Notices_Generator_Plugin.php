<?php
/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-notices_generator for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-notices_generator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Notices_Generator_Plugin' ) ) :

/**
 * Main class.
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @since 1.0
 */
class Notices_Generator_Plugin {
    /**
     * @const string Plugin's slug.
     */
    const SLUG = 'odwp-notices_generator';

    /**
     * @const string Plugin's version.
     */
    const VERSION = '1.0.0';

    /**
     * @const string
     */
    const SETTINGS_KEY = 'odwpng_settings';

    /**
     * @const string
     */
    const TABLE_NAME = 'odwpng';

    /**
     * @internal Activates the plugin.
     * @return void
     */
    public static function activate() {
        //...
    }

    /**
     * @internal Deactivates the plugin.
     * @return void
     */
    public static function deactivate() {
        //...
    }

    /**
     * @return array Default values for settings of the plugin.
     */
    public static function get_default_options() {
        return [];
    }

    /**
     * @return array Settings of the plugin.
     */
    public static function get_options() {
        $defaults = self::get_default_options();
        $options = get_option( self::SETTINGS_KEY, [] );
        $update = false;

        // Fill defaults for the options that are not set yet
        foreach( $defaults as $key => $val ) {
            if( ! array_key_exists( $key, $options ) ) {
                $options[$key] = $val;
                $update = true;
            }
        }

        // Updates options if needed
        if( $update === true) {
            update_option( self::SETTINGS_KEY, $options );
        }

        return $options;
    }

    /**
     * Returns value of option with given key.
     * @param string $key Option's key.
     * @return mixed Option's value.
     * @throws Exception Whenever option with given key doesn't exist.
     */
    public static function get_option( $key ) {
        $options = self::get_options();

        if( ! array_key_exists( $key, $options ) ) {
            throw new Exception( 'Option "'.$key.'" is not set!' );
        }

        return $options[$key];
    }

    /**
     * Initializes the plugin.
     * @return void
     */
    public static function initialize() {
        register_activation_hook( __FILE__, [__CLASS__, 'activate'] );
        register_deactivation_hook( __FILE__, [__CLASS__, 'deactivate'] );
        register_uninstall_hook( __FILE__, [__CLASS__, 'uninstall'] );

        add_action( 'init', [__CLASS__, 'init'] );
        add_action( 'admin_init', [__CLASS__, 'admin_init'] );
        add_action( 'admin_menu', [__CLASS__, 'admin_menu'] );
        add_action( 'plugins_loaded', [__CLASS__, 'plugins_loaded'] );
        add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts'] );
        add_action( 'admin_enqueue_scripts', [__CLASS__, 'admin_enqueue_scripts'] );
    }

    /**
     * Hook for "init" action.
     * @return void
     */
    public static function init() {
        // Initialize locales
        $path = dirname( __FILE__ ) . '/languages';
        load_plugin_textdomain( self::SLUG, false, $path );
        // Load custom post types
        self::init_cpt();
    }

    /**
     * Initialize custom post types.
     * @return void
     */
    public static function init_cpt() {
		$labels = array(
			'name' => _x( 'Oznámení', 'post type general name', self::SLUG ),
			'singular_name' => _x( 'Vytvořit oznámení', 'post type singular name', self::SLUG ),
			'add_new' => __( 'Nové oznámení', self::SLUG ),
			'add_new_item' => __( 'Vytvořit nové oznámení', self::SLUG ),
			'edit_item' => __( 'Upravit oznámení', self::SLUG ),
			'new_item' => __( 'Nové oznámení', self::SLUG ),
			'view_item' => __( 'Zobrazit oznámení', self::SLUG ),
			'search_items' => __( 'Prohledat oznámení', self::SLUG ),
			'not_found' => __( 'Žádná oznámení nebyly nalezeny.', self::SLUG ),
			'not_found_in_trash' => __( 'Žádná oznámení nebyly v koši nalezeny.', self::SLUG ),
			'all_items' => __( 'Všechny oznámení', self::SLUG ),
			'archives' => __( 'Archív oznámení', self::SLUG ),
			'menu_name' => __( 'Oznámení', self::SLUG ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'description' => __( 'Oznámení o úmrtí', self::SLUG ),
			'supports' => array( 'title', 'editor', 'revisions', 'custom-fields', 'page-attributes' ),
			'taxonomies' => array( 'post_tag' ),
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-clock',
			'show_in_nav_menus' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'query_var' => true,
			'can_export' => true,
	        'public' => true,
	        'has_archive' => true,
	        'capability_type' => 'post',
		);

		/**
		 * Filter "Notices" post type arguments.
		 *
		 * @since 1.0.0
		 * @param array $arguments "Notices" post type arguments.
		 */
		$args = apply_filters( 'odwpp_' . self::SLUG . '_post_type_arguments', $args );
		register_post_type( self::SLUG, $args );
    }

    /**
     * Hook for "admin_init" action.
     * @return void
     */
    public static function admin_init() {
        register_setting( self::SLUG, self::SETTINGS_KEY );

        $options = self::get_options();
        //...
    }

    /**
     * Hook for "admin_menu" action.
     * @return void
     */
    public static function admin_menu() {
        //...
    }

    /**
     * Hook for "admin_enqueue_scripts" action.
     * @param string $hook
     * @return void
     */
    public static function admin_enqueue_scripts( $hook ) {
        wp_enqueue_script( self::SLUG, plugins_url( 'js/admin.js', __FILE__ ), ['jquery'] );
        wp_localize_script( self::SLUG, 'odwpng', [
            //...
        ] );
        wp_enqueue_style( self::SLUG, plugins_url( 'css/admin.css', __FILE__ ) );
    }

    /**
     * Hook for "plugins_loaded" action.
     * @return void
     */
    public static function plugins_loaded() {
        //...
    }

    /**
     * Hook for "wp_enqueue_scripts" action.
     * @return void
     */
    public static function enqueue_scripts() {
        wp_enqueue_script( self::SLUG, plugins_url( 'js/public.js', __FILE__ ), ['jquery'] );
        wp_localize_script( self::SLUG, 'odwpng', [
            //...
        ] );
        wp_enqueue_style( self::SLUG, plugins_url( 'css/public.css', __FILE__ ) );
    }

    /**
     * @internal Uninstalls the plugin.
     * @return void
     */
    public static function uninstall() {
        if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }

        // Nothing to do...
    }

    /**
     * @internal Prints error message in correct WP amin style.
     * @param string $msg Error message.
     * @param string $type (Optional.) One of ['info','updated','error'].
     * @return void
     */
    protected static function print_admin_error( $msg, $type = 'info' ) {
        $avail_types = ['error', 'info', 'updated'];
        $_type = in_array( $type, $avail_types ) ? $type : 'info';
        printf( '<div class="%s"><p>%s</p></div>', $_type, $msg );
    }
} // End of Notices_Generator_Plugin

endif;