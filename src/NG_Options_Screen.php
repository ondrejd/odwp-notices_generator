<?php
/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-notices_generator for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-notices_generator
 */

if ( ! class_exists( 'NG_Options_Screen' ) ):

/**
 * @since 0.0.1
 */
class NG_Options_Screen extends NG_Screen_Prototype {
    /**
     * Constructor.
     * @param WP_Screen $screen Optional.
     * @return void
     */
    public function __construct( \WP_Screen $screen = null ) {
        // Main properties
        $this->slug = NG_SLUG . '-plugin_options';
        $this->menu_title = __( 'Smuteční oznámení', NG_SLUG );
        $this->page_title = __( 'Nastavení pro plugin <em>Smuteční oznámení</em>', NG_SLUG );

        // Specify help tabs
        $this->help_tabs[] = array(
            'id'      => $this->slug . '-help_tab',
            'title'   => __( 'Tables', NG_SLUG ),
            'content' => __( '<p style"colof: #f30;"><code>XXX</code> Fill this screen help!<p>', NG_SLUG ),
        );

        // Specify help sidebars
        $this->help_sidebars[] = sprintf(
            __( '<b>Usefull links</b><p><a href="%1$s" target="blank"><code>WP_List_Table</code></a> on <b>WordPress Codex</b>.</p><p><a href="%2$s" target="blank"><code>WP_List_Table</code></a> on <b>WordPress Code Reference</b>.</a></p><!-- <p><a href="%3$s" target="blank">Link 3</a> is the third link.</p> -->', NG_SLUG ),
            'http://codex.wordpress.org/Class_Reference/WP_List_Table',
            'https://developer.wordpress.org/reference/classes/wp_list_table/',
            '#'
        );

        // Finish screen constuction
        parent::__construct( $screen );
    }

    /**
     * Action for `admin_menu` hook.
     * @return void
     */
    public function admin_menu() {
        $this->hookname = add_options_page(
                $this->page_title,
                $this->menu_title,
                'manage_options',
                $this->slug,
                [$this, 'render']
        );

        add_action( 'load-' . $this->hookname, [$this, 'screen_load'] );
    }
}

endif;
