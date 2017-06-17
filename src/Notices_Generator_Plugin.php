<?php
/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-notices_generator for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-notices_generator
 *
 * @todo Přidat tlačítko do editoru pro shortcode!
 * @todo Přidat nastavení pluginu (včetně nastavení obrázků, veršů atp. pro editor)
 * @todo Oznámení musí fungovat jako samostatný typ. Akorát v administraci bude 
 *       skrytá defaultní položka menu "Nové oznámení" a místo toho tam bude přidán
 *       odkaz, který povede na front-end.
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
     * @var string The first value for screen options for options page.
     */
    const OPTIONS_SS_COMPACT = 'compact';

    /** 
     * @var string The second value for screen options for options page.
     */
    const OPTIONS_SS_FULL = 'full';

    /**
     * @var string
     */
    public static $options_page_hook;

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
        return [
            'new_notices_only_logged_users' => false,
            'save_notices_from_unknown_users' => true,
            'notice_borders' => self::get_default_notice_borders(),
            'notice_images' => self::get_default_notice_images(),
            'verses' => self::get_default_verses(),
        ];
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

        add_shortcode( 'notice_designer', [__CLASS__, 'shortcode_notice_designer_render'] );
    }

    /**
     * Renders notice designer shortcode.
     * @param array $attributes
     * @return string
     */
    public static function shortcode_notice_designer_render( $attributes ) {
        $attrs = shortcode_atts( [
            // Notice ID to edit, if is set than we edit notice not creating new one.
            'notice_id' => 0,
        ], $attributes );

        ob_start( function() {} );

        // Get default variables
        $borders = self::get_notice_borders();
        $images = self::get_notice_images();
        $verses = self::get_verses();
        
        include( NG_PATH . 'partials/shortcode-notices_generator.phtml' );
        $html = ob_get_flush();
        return apply_filters( 'odwpng-notices_generator', $html );
    }

    /**
     * Hook for "init" action.
     * @return void
     */
    public static function init() {
        // Initialize locales
        $path = NG_PATH . 'languages';
        load_plugin_textdomain( NG_SLUG, false, $path );
        // Load custom post types
        self::init_cpt();
    }

    /**
     * Initialize custom post types.
     * @return void
     */
    public static function init_cpt() {
        $labels = [
            'name' => __( 'Oznámení', NG_SLUG ),
            'singular_name' => __( 'Vytvořit oznámení', NG_SLUG ),
            'add_new' => __( 'Nové oznámení', NG_SLUG ),
            'add_new_item' => __( 'Vytvořit nové oznámení', NG_SLUG ),
            'edit_item' => __( 'Upravit oznámení', NG_SLUG ),
            'new_item' => __( 'Nové oznámení', NG_SLUG ),
            'view_item' => __( 'Zobrazit oznámení', NG_SLUG ),
            'search_items' => __( 'Prohledat oznámení', NG_SLUG ),
            'not_found' => __( 'Žádná oznámení nebyly nalezeny.', NG_SLUG ),
            'not_found_in_trash' => __( 'Žádná oznámení nebyly v koši nalezeny.', NG_SLUG ),
            'all_items' => __( 'Všechny oznámení', NG_SLUG ),
            'archives' => __( 'Archív oznámení', NG_SLUG ),
            'menu_name' => __( 'Oznámení', NG_SLUG ),
        ];

        $args = [
            'labels' => $labels,
            'hierarchical' => false,
            'description' => __( 'Oznámení o úmrtí', NG_SLUG ),
            'supports' => array( 'title', 'editor', 'revisions', 'custom-fields', 'page-attributes' ),
            'taxonomies' => [],
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-format-aside',
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'query_var' => true,
            'can_export' => true,
            'public' => true,
            'has_archive' => true,
            'capability_type' => 'post',
        ];

        /**
         * Filter "Notices" post type arguments.
         *
         * @since 1.0.0
         * @param array $arguments "Notices" post type arguments.
         */
        $args = apply_filters( 'odwng_notices_post_type_arguments', $args );
        register_post_type( 'funeral_notices', $args );
    }

    /**
     * Hook for "admin_init" action.
     * @return void
     */
    public static function admin_init() {
        register_setting( NG_SLUG, self::SETTINGS_KEY );

        $options = self::get_options();

        $section1 = self::SETTINGS_KEY . '_section_1';
        add_settings_section(
                $section1,
                __( 'Obecné nastavení pluginu' ),
                [__CLASS__, 'render_settings_section_1'],
                NG_SLUG
        );

        add_settings_field(
                'new_notices_only_logged_users',
                __( 'Vytváření oznámení', NG_SLUG ),
                [__CLASS__, 'render_setting_only_logged_users'],
                NG_SLUG,
                $section1
        );

        add_settings_field(
                'save_notices_from_unknown_users',
                __( 'Ukládání oznámení', NG_SLUG ),
                [__CLASS__, 'render_setting_unknown_users'],
                NG_SLUG,
                $section1
        );

        $section2 = self::SETTINGS_KEY . '_section_2';
        add_settings_section(
                $section2,
                __( 'Defaultní hodnoty' ),
                [__CLASS__, 'render_settings_section_2'],
                NG_SLUG
        );

        add_settings_field(
                'notice_borders',
                __( 'Okraje oznámení', NG_SLUG ),
                [__CLASS__, 'render_setting_notice_borders'],
                NG_SLUG,
                $section2
        );

        add_settings_field(
                'notice_images',
                __( 'Obrázky pro oznámení', NG_SLUG ),
                [__CLASS__, 'render_setting_notice_images'],
                NG_SLUG,
                $section2
        );

        add_settings_field(
                'verses',
                __( 'Smuteční verše', NG_SLUG ),
                [__CLASS__, 'render_setting_verses'],
                NG_SLUG,
                $section2
        );

        // Save screen options on the options page
        add_filter( 'set-screen-option', [__CLASS__, 'set_screen_option'], 10, 3 );
    }

    /**
     * Hook for "admin_menu" action.
     * @return void
     */
    public static function admin_menu() {
        Notices_Generator_Plugin::$options_page_hook = add_options_page(
                __( 'Nastavení pro plugin Smuteční oznámení', NG_SLUG ),
                __( 'Smuteční oznámení', NG_SLUG ),
                'manage_options',
                NG_SLUG . '-options', // Result is "settings_page_odwp-notices_generator-options".
                [__CLASS__, 'admin_options_page']
        );

        /**
         * @param string $settings
         * @param \WP_Screen $screen
         */
        add_filter( 'screen_settings', function( $settings, \WP_Screen $screen ) {
            if( $screen->base !== Notices_Generator_Plugin::$options_page_hook ) {
                return $settings;
            }

            $display_style = filter_input( INPUT_GET, 'display_style' );

            return sprintf(
                    '<label for="odwpng-display_style">' . __( 'Styl zobrazení:', NG_SLUG ) . '</label>' .
                    '<select id="odwpng-display_style" name="display_style">' .
                        '<option value="' . Notices_Generator_Plugin::OPTIONS_SS_COMPACT . '">' . __( 'Kompaktní', NG_SLUG ) . '</option>' .
                        '<option value="' . Notices_Generator_Plugin::OPTIONS_SS_FULL . '">' . __( 'Plné', NG_SLUG ) . '</option>' .
                    '</select>' .
                    get_submit_button( __( 'Ulož', NG_SLUG ), 'secondary', 'submit-button', false ),
                    $display_style
            );
        }, 10, 2 );
    }

    /**
     * Hook for "set-screen-option" filter.
     * @return void
     */
    public static function set_screen_options() {
        //...
    }

    /**
     * Hook for "admin_enqueue_scripts" action.
     * @param string $hook
     * @return void
     */
    public static function admin_enqueue_scripts( $hook ) {
        wp_enqueue_script( NG_SLUG, plugins_url( 'js/admin.js', NG_PATH ), ['jquery'] );
        wp_localize_script( NG_SLUG, 'odwpng', [
            //...
        ] );
        wp_enqueue_style( NG_SLUG, plugins_url( 'css/admin.css', NG_PATH ) );
    }

    /**
     * Renders plugin's options page.
     * @return void
     */
    public static function admin_options_page() {
        ob_start( function() {} );
        include( NG_PATH . 'partials/settings-page.phtml');
        echo ob_get_flush();
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
        wp_enqueue_script( NG_SLUG, plugins_url( 'js/public.js', NG_PATH ), ['jquery'] );
        wp_localize_script( NG_SLUG, 'odwpng', [
            //...
        ] );
        wp_enqueue_style( NG_SLUG, plugins_url( 'css/public.css', NG_PATH ) );
    }

    /**
     * @internal Renders the first settings section.
     * @return void
     */
    public static function render_settings_section_1() {
        ob_start( function() {} );
        include( NG_PATH . 'partials/settings-section_1.phtml' );
        echo ob_get_flush();
    }

    /**
     * @internal Renders setting `new_notices_only_logged_users`.
     * @return void
     *
     * @todo V budoucnu možno i specifikovat pro určité role...
     */
    public static function render_setting_only_logged_users() {
        $only_logged_users = ( bool ) self::get_option( 'new_notices_only_logged_users' );
        ob_start( function() {} );
        include( NG_PATH . 'partials/setting-only_logged_users.phtml' );
        echo ob_get_flush();
    }

    /**
     * @internal Renders setting `save_notices_from_unknown_users`.
     * @return void
     *
     * @todo Udělat jinak - možnosti "Ukládat všechna oznámení", "Ukládat oznámení přihlášených uživatelů", "Neukládat žádná oznámení".
     */
    public static function render_setting_unknown_users() {
        $unknown_users = ( bool ) self::get_option( 'save_notices_from_unknown_users' );
        ob_start( function() {} );
        include( NG_PATH . 'partials/setting-unknown_users.phtml' );
        echo ob_get_flush();
    }

    /**
     * @internal Renders the second settings section.
     * @return void
     */
    public static function render_settings_section_2() {
        ob_start( function() {} );
        include( NG_PATH . 'partials/settings-section_2.phtml' );
        echo ob_get_flush();
    }

    /**
     * @internal Renders setting `notice_borders`.
     * @return void
     */
    public static function render_setting_notice_borders() {
        $borders = self::get_notice_borders();
        ob_start( function() {} );
        include( NG_PATH . '/partials/setting-notice_borders.phtml' );
        echo ob_get_flush();
    }

    /**
     * @internal Renders setting `notice_images`.
     * @return void
     */
    public static function render_setting_notice_images() {
        $images = self::get_notice_images();
        ob_start( function() {} );
        include( NG_PATH . 'partials/setting-notice_images.phtml' );
        echo ob_get_flush();
    }

    /**
     * @internal Renders setting `verses`.
     * @return void
     */
    public static function render_setting_verses() {
        $verses = self::get_verses();
        ob_start( function() {} );
        include( NG_PATH . 'partials/setting-verses.phtml' );
        echo ob_get_flush();
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

    /**
     * @internal Returns array with notice images.
     * @return array
     */
    protected static function get_notice_images() {
        $images = self::get_option( 'notice_images' );

        if( ! is_array( $images ) ) {
            $images = self::get_default_notice_images();
        }

        return apply_filters( 'odwpng_notice_images', $images );
    }

    /**
     * @internal Returns array with notice borders.
     * @return array
     */
    protected static function get_notice_borders() {
        $borders = self::get_option( 'notice_borders' );

        if( ! is_array( $borders ) ) {
            $borders = self::get_default_notice_borders();
        }

        return apply_filters( 'odwpng_notice_borders', $borders );
    }

    /**
     * @internal Returns array with funeral verses.
     * @return array
     */
    protected static function get_verses() {
        $verses  = self::get_option( 'verses' );

        if( ! is_array( $verses ) ) {
            $verses = self::get_default_verses();
        }

        return apply_filters( 'odwpng_verses', $verses );
    }

    /**
     * @internal Returns default notice images.
     * @return array
     */
    protected static function get_default_notice_images() {
        $images = [
            1  => plugins_url( 'img/notice-img01.jpg', NG_PATH ),
            2  => plugins_url( 'img/notice-img02.jpg', NG_PATH ),
            3  => plugins_url( 'img/notice-img03.jpg', NG_PATH ),
            4  => plugins_url( 'img/notice-img04.jpg', NG_PATH ),
            5  => plugins_url( 'img/notice-img05.jpg', NG_PATH ),
            6  => plugins_url( 'img/notice-img06.jpg', NG_PATH ),
            7  => plugins_url( 'img/notice-img07.jpg', NG_PATH ),
            8  => plugins_url( 'img/notice-img08.jpg', NG_PATH ),
            9  => plugins_url( 'img/notice-img09.jpg', NG_PATH ),
            10 => plugins_url( 'img/notice-img10.jpg', NG_PATH ),
        ];

        return apply_filters( 'odwpng_default_notice_images', $images );
    }

    /**
     * @internal Returns default notice borders.
     * @return array
     */
    protected static function get_default_notice_borders() {
        $borders = [
            1  => plugins_url( 'img/notice-border01.jpg', NG_PATH ),
            2  => plugins_url( 'img/notice-border02.jpg', NG_PATH ),
            3  => plugins_url( 'img/notice-border03.jpg', NG_PATH ),
            4  => plugins_url( 'img/notice-border04.jpg', NG_PATH ),
        ];

        return apply_filters( 'odwpng_default_notice_borders', $borders );
    }

    /**
     * @internal Returns default funeral verses for the generator.
     * @return array
     */
    protected static function get_default_verses() {
        $verses = [
            0  => __( "Až umřu, nic na tomto světě\nse nestane a nezmění,\njen srdcí několik se zachvěje v rose\njak k ránu květiny...\n\n<em>J. Wolker</em>", NG_SLUG ),
            1  => __( "Smrti se nebojím, smrt není zlá,\nsmrt je jen kus života těžkého.\nCo strašné je, co zlé je,\nto umírání je.\n\n<em>J. Wolker</em>", NG_SLUG ),
            2  => __( "A za vše, za vše dík.\nZa lásku, jaká byla,\nza život, jaký byl..\n\n<em>Donát Šajner</em>", NG_SLUG ),
            3  => __( "Buď vůle tvá...", NG_SLUG ),
            4  => __( "Nezemřel jsem, neboť vím,\nže budu stále žít v srdcích těch,\nkteří mě milovali.", NG_SLUG ),
            5  => __( "Kdo v srdci žije, neumírá.\n\n<em>František Hrubín</em>", NG_SLUG ),
            6  => __( "Hospodin je blízko všem, kteří volají k Němu.\n\n<em>Žalm 145,18 Bible</em>", NG_SLUG ),
            7  => __( "Ježíš jí řekl: „Já jsem vzkříšení a život. Kdo věří ve mne, i kdyby umřel, bude žít.\"\n\n<em>Jan 11,25 Bible</em>", NG_SLUG ),
            8  => __( "Každý, kdo vzývá jméno Páně, bude spasen.	\n\n<em>Římanům 10,13 Bible</em>", NG_SLUG ),
            9  => __( "Kdo ke Mně přijde, toho nevyženu ven.\n\n<em>Jan 6,37 Bible</em>", NG_SLUG ),
            10 => __( "Kdo věří v Syna, má život věčný.\n\n<em>Jan 3,36 Bible</em>", NG_SLUG ),
            11 => __( "Má spása a sláva je v Bohu, On je má mocná skála, v Bohu mám útočiště.\n\n<em>Žalm 62,8 Bible</em>", NG_SLUG ),
            12 => __( "Mně těžká slova v hrdle váznou, když říci mám Ti: Sbohem buď\n\n<em>Jaroslav Vrchlický</em>", NG_SLUG ),
            13 => __( "Moje ovce slyší můj hlas, já je znám, jdou za mnou a já jim dávám věčný život: nezahynou navěky a nikdo je z mé ruky nevyrve.\n\n<em>Jan 10, 27-28 Bible</em>", NG_SLUG ),

        ];

        return apply_filters( 'odwpng_default_verses', $verses );
    }
} // End of Notices_Generator_Plugin

endif;
