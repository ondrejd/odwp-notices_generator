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
    public static function init() {
        register_activation_hook( __FILE__, [__CLASS__, 'activate'] );
        register_deactivation_hook( __FILE__, [__CLASS__, 'deactivate'] );
        register_uninstall_hook( __FILE__, [__CLASS__, 'uninstall'] );

        add_action( 'init', [__CLASS__, 'init_textdomain'] );
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
    public static function init_textdomain() {
        $path = dirname( __FILE__ ) . '/languages';
        load_plugin_textdomain( self::SLUG, false, $path );
    }

    /**
     * Hook for "admin_init" action.
     * @return void
     */
    public static function admin_init() {
        register_setting( self::SLUG, self::SETTINGS_KEY );

        $options = self::get_options();
        self::$sendlane = new Sendlane_Api( $options );

        $section1 = self::SETTINGS_KEY . '_section_1';
        add_settings_section(
                $section1,
                __( 'Sendlane API' ),
                [__CLASS__, 'render_settings_section_1'],
                self::SLUG
        );

        add_settings_field(
                'api_key',
                __( 'API klíč', self::SLUG ),
                [__CLASS__, 'render_setting_api_key'],
                self::SLUG,
                $section1
        );

        add_settings_field(
                'hash_key',
                __( '<em>Hash</em> klíč', self::SLUG ),
                [__CLASS__, 'render_setting_hash_key'],
                self::SLUG,
                $section1
        );

        add_settings_field(
                'domain',
                __( 'Doména', self::SLUG ),
                [__CLASS__, 'render_setting_domain'],
                self::SLUG,
                $section1
        );
    }

    /**
     * Hook for "admin_menu" action.
     * @return void
     */
    public static function admin_menu() {
        add_menu_page(
                __( 'Sendlane plugin', self::SLUG ),
                __( 'Sendlane plugin', self::SLUG ),
                'manage_options',
                'odwpng_menu',
                [__CLASS__, 'render_admin_page_list'],
                null,
                100
        );
        add_submenu_page(
                'odwpng_menu',
                __( 'Přidat akci', self::SLUG ),
                __( 'Přidat akci', self::SLUG ),
                'manage_options',
                'odwpng_menu_add',
                [__CLASS__, 'render_admin_page_add']
        );
        add_options_page(
                __( 'Nastavení pro Sendlane plugin', self::SLUG ),
                __( 'Sendlane plugin', self::SLUG ),
                'manage_options',
                self::SLUG . '-options',
                [__CLASS__, 'admin_options_page']
        );
    }

    /**
     * Hook for "admin_enqueue_scripts" action.
     * @param string $hook
     * @return void
     */
    public static function admin_enqueue_scripts( $hook ) {
        wp_enqueue_script( self::SLUG, plugins_url( 'assets/js/admin.js', __FILE__ ), ['jquery'] );
        wp_localize_script( self::SLUG, 'odwpng', [
            //...
        ] );
        wp_enqueue_style( self::SLUG, plugins_url( 'assets/css/admin.css', __FILE__ ) );
    }

    /**
     * Renders plugin's options page.
     * @return void
     */
    public static function admin_options_page() {
?>
<form action="options.php" method="post">
    <h2><?php _e( 'Nastavení pro Sendlane plugin', self::SLUG ) ?></h2>
<?php
        settings_fields( self::SLUG );
        do_settings_sections( self::SLUG );
        submit_button();
?>
</form>
<?php
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
        //...
    }

    /**
     * @return array Sendlane lists.
     */
    public static function get_lists() {
        return self::$sendlane->lists();
    }

    /**
     * @return array Sendlane tags.
     */
    public static function get_tags() {
        return self::$sendlane->tags();
    }

    /**
     * Renders settings section 1.
     * @return void
     */
    public static function render_settings_section_1() {
?>
<p class="description">
    <?php printf(
            __( 'Pro přístup ke službě <b>Sendlane</b> potřebujete <abbr title="Application Program Interface">API</abbr> a <em>hash</em> klíč (více na stránce <a href="%s" target="blank">What is an API Key?</a>). Nezapomeňte zadat také příslušnou doménu pro zadané klíče.', self::SLUG ),
            'http://help.sendlane.com/knowledgebase/api-key/'
    ) ?>
</p>
<?php
    }

    /**
     * Renders input for "api_key" setting.
     * @return void
     */
    public static function render_setting_api_key() {
        $options = self::get_options();
?>
<input type="text" name="odwpng_settings[api_key]" value="<?= $options['api_key'] ?>" class="regular-text">
<?php
    }

    /**
     * Renders input for "hash_key" setting.
     * @return void
     */
    public static function render_setting_hash_key() {
        $options = self::get_options();
?>
<input type="text" name="odwpng_settings[hash_key]" value="<?= $options['hash_key'] ?>" class="regular-text">
<?php
    }

    /**
     * Renders input for "domain" setting.
     * @return void
     */
    public static function render_setting_domain() {
        $options = self::get_options();
?>
<input type="text" name="odwpng_settings[domain]" value="<?= $options['domain'] ?>" class="regular-text">
<?php
    }

    /**
     * Renders plugin's administration page "List pages".
     * @return void
     */
    public static function render_admin_page_list() {
?>
<div class="wrap">
    <h1><?php _e( 'Sendlane plugin', self::SLUG ) ?></h1>
    <p class="description"><?php _e( 'Zde můžete nastavit cílové stránky a akce k nim připojené.', self::SLUG ) ?></p>
    <form id="odwpng-actions_table_form" method="get">
        <table class="wp-list-table widefat fixed striped odwpng-actions">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Označit vše', self::SLUG ) ?></label>
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th scope="col" id="page" class="manage-column column-page column-primary"><?php _e( 'Cílová stránka', self::SLUG ) ?></th>
                    <th scope="col" id="action" class="manage-column column-action"><?php _e( 'Akce', self::SLUG ) ?></th>
                    <th scope="col" id="list_tag" class="manage-column column-list_tag"><?php _e( 'Seznam/tag', self::SLUG ) ?></th>
                </tr>
            </thead>
            <tbody id="the-list">
                <tr>
                    <td colspan="4"><?php _e( 'Zatím nejsou vytvořeny žádné akce.', self::SLUG ) ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Označit vše', self::SLUG ) ?></label>
                        <input id="cb-select-all-2" type="checkbox">
                    </td>
                    <th scope="col" id="page" class="manage-column column-page column-primary"><?php _e( 'Cílová stránka', self::SLUG ) ?></th>
                    <th scope="col" id="action" class="manage-column column-action"><?php _e( 'Akce', self::SLUG ) ?></th>
                    <th scope="col" id="list_tag" class="manage-column column-list_tag"><?php _e( 'Seznam/tag', self::SLUG ) ?></th>
                </tr>
            </tfoot>
        </table>
    </form>
</div>
<?php
    }

    /**
     * Renders plugin's administration page "Add page".
     * @return void
     * @todo Add `wpnonce` for the security!
     */
    public static function render_admin_page_add() {
        $avail_pages = get_pages( [
            'sort_order' => 'asc',
            'hierarchical' => 0,
            'child_of' => 0,
            'post_type' => 'page',
        ] );
        $avail_lists = self::get_lists();
        $avail_tags = self::get_tags();
        $api_error = array_key_exists( 'error', $avail_lists ) || array_key_exists( 'error', $avail_tags );
?>
<div class="wrap">
    <h1><?php _e( 'Přidat akci', self::SLUG ) ?></h1>
    <?php if( array_key_exists( 'error', $avail_lists ) ) : 
    foreach( $avail_lists['error'] as $msg) : 
        self::print_error( __( '<b>Sendlane API error:</b>&nbsp;', self::SLUG ) . $msg, 'error' );
    endforeach; 
    endif; ?>
    <?php if( array_key_exists( 'error', $avail_tags ) ) : 
    foreach( $avail_tags['error'] as $msg) : 
        self::print_error( __( '<b>Sendlane API error:</b>&nbsp;', self::SLUG ) . $msg, 'error' );
    endforeach; 
    endif; ?>
    <form method="post" action="<?php echo admin_url( '?page=odwpng_menu_add' ) ?>" novalidate="novalidate">
        <input type="hidden" name="_wpnonce" value="">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="odwpng-page"><?php _e( 'Cílová stránka', self::SLUG ) ?></label>
                    </th>
                    <td>
                        <select id="odwpng-page" name="page">
                            <?php foreach( $avail_pages as $page ) : ?>
                            <option value="<?php echo $page->ID ?>"><?php echo $page->post_title ?></option>
                            <?php endforeach ?>
                        </select>
                        <p class="description"><?php _e( 'Vyberte cílovou stránku.', self::SLUG ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="odwpng-action"><?php _e( 'Akce', self::SLUG ) ?></label>
                    </th>
                    <td>
                        <select id="odwpng-action" name="action">
                            <option value="subscribe"><?php _e( 'Přihlásit', self::SLUG ) ?></option>
                            <option value="unsubscribe"><?php _e( 'Odhlásit', self::SLUG ) ?></option>
                            <option value="tag_add"><?php _e( 'Přidat tag', self::SLUG ) ?></option>
                            <option value="tag_remove"><?php _e( 'Odebrat tag', self::SLUG ) ?></option>
                        </select>
                        <p class="description"><?php _e( 'Akce k provedení přes Sendlane API', self::SLUG ) ?></p>
                    </td>
                </tr>
                <tr id="odwpng-lists_row">
                    <th scope="row">
                        <label for="odwpng-lists"><?php _e( 'Seznam', self::SLUG ) ?></label>
                    </th>
                    <td>
                        <?php if( $api_error === true ) : ?>
                        <select id="odwpng-lists" name="lists" disabled="disabled"></select>
                        <p class="description" style="color: #f30;"><?php _e( 'Nastala chyba při získávání dat prostřednictvím <b>Sendlane API</b>!', self::SLUG ) ?></p>
                        <?php else : ?>
                        <select id="odwpng-lists" name="lists">
                            <?php foreach( $avail_lists as $list ) : ?>
                            <option value="<?php echo $list['list_id'] ?>"><?php echo $list['list_name'] ?></option>
                            <?php endforeach ?>
                        </select>
                        <?php endif ?>
                    </td>
                </tr>
                <tr id="odwpng-tags_row">
                    <th scope="row">
                        <label for="odwpng-tags"><?php _e( 'Tag', self::SLUG ) ?></label>
                    </th>
                    <td>
                        <?php if( $api_error === true ) : ?>
                        <select id="odwpng-tags" name="tags" disabled="disabled"></select>
                        <p class="description" style="color: #f30;"><?php _e( 'Nastala chyba při získávání dat prostřednictvím <b>Sendlane API</b>!', self::SLUG ) ?></p>
                        <?php else : ?>
                        <select id="odwpng-tags" name="tags">
                            <?php foreach( $avail_tags as $tag ) : ?>
                            <option value="<?php echo $tag['tag_id'] ?>"><?php echo $tag['tag_name'] ?></option>
                            <?php endforeach ?>
                        </select>
                        <?php endif ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Přidat akci">&nbsp;
            <input type="reset" name="reset" id="reset" class="button button-cancel" value="Zrušit">
        </p>
    </form>
</div>
<?php
    }

    /**
     * @internal Uninstalls the plugin.
     * @return void
     */
    private static function uninstall() {
        if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }

        // Nothing to do...
    }

    /**
     * @internal Check requirements of the plugin.
     * @link https://developer.wordpress.org/reference/functions/is_plugin_active_for_network/#source-code
     * @return boolean Returns TRUE if requirements are met.
     * @todo Current solution doesn't work for WPMU...
     */
    public static function requirements_check() {
        //$active_plugins = (array) get_option( 'active_plugins', [] );
        //return in_array( 'woocommerce/woocommerce.php', $active_plugins ) ? true : false;
        return true;
    }

    /**
     * @internal Shows error in WP administration that minimum requirements were not met.
     * @return void
     */
    public static function requirements_error() {
        //self::print_error( __( 'Plugin <b>Úpravy pro Estets.cz</b> vyžaduje, aby byl nejprve nainstalovaný a aktivovaný plugin <b>WooCommerce</b>.', 'odwpwcgp' ), 'error' );
        //self::print_error( __( 'Plugin <b>Úpravy pro Estets.cz</b> byl <b>deaktivován</b>.', 'odwpwcgp' ), 'updated' );
    }

    /**
     * @internal Prints error message in correct WP amin style.
     * @param string $msg Error message.
     * @param string $type (Optional.) One of ['info','updated','error'].
     * @return void
     */
    protected static function print_error( $msg, $type = 'info' ) {
        $avail_types = ['error', 'info', 'updated'];
        $_type = in_array( $type, $avail_types ) ? $type : 'info';
        printf( '<div class="%s"><p>%s</p></div>', $_type, $msg );
    }
} // End of Notices_Generator_Plugin

endif;