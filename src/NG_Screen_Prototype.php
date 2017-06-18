<?php
/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-notices_generator for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-notices_generator
 */

if( ! class_exists( 'NG_Screen_Prototype' ) ):

/**
 * @since 1.0
 */
class NG_Screen_Prototype {
    /**
     * @var string $slug
     */
    protected $slug;

    /**
     * @var string $page_title
     */
    protected $page_title;

    /**
     * @var string $menu_title
     */
    protected $menu_title;

    /**
     * @var array
     */
    protected $help_tabs = array();

    /**
     * @var array
     */
    protected $help_sidebars = array();

    /**
     * @internal
     * @var string $hookname Name of the admin menu page hook.
     */
    protected $hookname;

    /**
     * @var \WP_Screen $screen
     */
    protected $screen;

    /**
     * @param \WP_Screen $screen Optional.
     * @return void
     */
    public function __construct( \WP_Screen $screen = null ) {
        $this->screen = $screen;
        $this->help_tabs[] = [
            'id' => $this->slug . '-options_help_tab',
            'title' => __( 'Screen options', NG_SLUG ),
            'content' => sprintf(
                __( '<h4>Screen options</h4><p>Pay attention to screen options - there is a setting named <b>Used template</b> - if you don\'t know what they are you can choose them and see in <b>Generated Code</b> if they fit your needs. You can choose source codes template by your needs. If not these templates can be also extended via filter - for more details see <a href="%1$s" target="blank">documentation</a>.</p>', NG_SLUG ), '#'
            ),
        ];
    }

    /**
     * @return string
     */
    public function get_slug() {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function get_page_title() {
        return $this->page_title;
    }

    /**
     * @return string
     */
    public function get_menu_title() {
        return $this->menu_title;
    }

    /**
     * @return \WP_Screen
     */
    public function get_screen() {
        if( ! ( $this->screen instanceof \WP_Screen )) {
            $this->screen = get_current_screen();
        }

        return $this->screen;
    }

    /**
     * Returns current screen options.
     * @return array
     */
    public function get_screen_options() {
        $screen = $this->get_screen();
        $user = get_current_user_id();

        $display_description = get_user_meta( $user, $this->slug . '-display_description', true );

        if( strlen( $display_description ) == 0 ) {
            $display_description = $screen->get_option( $this->slug . '-display_description', 'default' );
        }

        $used_template = get_user_meta( $user, $this->slug . '-used_template', true );

        if( strlen( $used_template ) == 0 ) {
            $used_template = $screen->get_option( $this->slug . '-used_template', 'default' );
        }

        return [
            'display_description' => (bool) $display_description,
            'used_template' => $used_template,
        ];
    }

    /**
     * @internal
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function update_option( $key, $value ) {
        $options = Notices_Generator_Plugin::get_options();
        $need_update = false;

        if( ! array_key_exists( $key, $options ) ) {
            $need_update = true;
        }

        if( ! $need_update && $options[$key] != $value ) {
            $need_update = true;
        }

        if( $need_update === true) {
            $options[$key] = $value;
            update_option( $key, $value );
        }
    }

    /**
     * Action for `init` hook.
     * @return void
     */
    public function init() {
        add_action( 'admin_init', [$this, 'save_screen_options'] );
    }

    /**
     * Action for `admin_init` hook.
     * @return void
     */
    public function admin_init() {
        // ...
    }

    /**
     * Action for `init` hook.
     * @return void
     */
    public function admin_enqueue_scripts() {
        // ...
    }

    /**
     * Action for `admin_head` hook.
     * @return void
     */
    public function admin_head() {
        // ...
    }

    /**
     * Action for `admin_menu` hook.
     * @return void
     */
    public function admin_menu() {
        $this->hookname = add_submenu_page(
            'edit.php?post_type=wizard', $this->page_title, $this->menu_title, 'manage_options', $this->slug, [$this, 'render']
        );

        add_action( 'load-' . $this->hookname, [$this, 'screen_load'] );
    }

    /**
     * Action for `load-{$hookname}` hook (see {@see NG_Screen_Prototype::admin_menu} for more details).
     *
     * Creates screen help and add filter for screen options.
     *
     * @return void
     */
    public function screen_load() {
        $screen = $this->get_screen();

        // Screen help
        foreach( $this->help_tabs as $tab ) {
            $screen->add_help_tab( $tab );
        }

        foreach( $this->help_sidebars as $sidebar ) {
            $screen->set_help_sidebar( $sidebar );
        }

        // Screen options
        add_filter( 'screen_layout_columns', [$this, 'screen_options'] );

        $screen->add_option( $this->slug . '-display_description', [
            'label' => __( 'Display detail descriptions?', NG_SLUG ),
            'default' => 1,
            'option' => $this->slug . '-display_description'
        ] );
        $screen->add_option($this->slug . '-used_template', [
            'label' => __( 'Used source codes template', NG_SLUG ),
            'default' => 'default',
            'option' => $this->slug . '-used_template'
        ] );
    }

    /**
     * Renders screen options form. Handler for `screen_layout_columns` filter
     * (see {@see NG_Screen_Prototype::screen_load}).
     * @return void
     */
    public function screen_options() {
        // These are used in the template:
        $slug = $this->slug;
        $screen = $this->get_screen();
        extract( $this->get_screen_options() );
        $templates = $this->get_source_templates();

        ob_start();
        include( NG_PATH . 'partials/screen_options-wizard.phtml' );
        $output = ob_get_clean();

        /**
         * Filter for wizard's screen options form.
         *
         * Name of filter corresponds with slug of the particular wizard.
         * For example for `Custom Post Type wizard` is filter name
         * "devhelper_cpt_wizard_screen_options_form".
         *
         * @param string $output Rendered HTML.
         */
        $output = apply_filters( NG_SLUG . "_{$this->slug}_screen_options_form", $output );
        echo $output;
    }

    /**
     * Returns array with available source code templates.
     * @return array
     */
    public function get_source_templates() {
        $templates = [
            'default' => __( 'Default', NG_SLUG ),
        ];

        /**
         * Filter the templates used for the DevHelper wizard.
         *
         * Name of filter corresponds with slug of the particular wizard.
         * For example for `Custom Post Type wizard` is filter name
         * "devhelper_cpt_wizard_templates".
         *
         * @param array $templates Array with templates provided by DevHelper.
         */
        return apply_filters( NG_SLUG . "_{$this->slug}_templates", $templates );
    }

    /**
     * Action for `admin_init` hook (see {@see NG_Screen_Prototype::init} for more details).
     *
     * Saves screen options.
     *
     * @return void
     */
    public function save_screen_options() {
        $user = get_current_user_id();

        if(
                filter_input( INPUT_POST, $this->slug . '-submit' ) &&
                (bool) wp_verify_nonce( filter_input( INPUT_POST, $this->slug . '-nonce' ) ) === true
        ) {
            $_display_description = (string) filter_input( INPUT_POST, $this->slug . '-checkbox1' );
            $display_description = ( strtolower( $_display_description ) == 'on' ) ? 1 : 0;
            update_user_meta( $user, $this->slug . '-display_description', $display_description );

            $used_template = (string) filter_input( INPUT_POST, $this->slug . '-select1' );
            update_user_meta( $user, $this->slug . '-used_template', $used_template );
        }
    }

    /**
     * Render page self.
     * @param array $args (Optional.) Arguments for rendered template.
     * @return void
     */
    public function render( $args = [] ) {
        // These are used in the template:
        $slug = $this->slug;
        $screen = $this->get_screen();
        $wizard = $this;
        extract( $this->get_screen_options() );
        extract( is_array( $args ) ? $args : [] );

        ob_start();
        include( NG_PATH . 'partials/screen-' . $this->slug . '.phtml' );
        $output = ob_get_clean();

        /**
         * Filter for whole wizard form.
         *
         * Name of filter corresponds with slug of the particular wizard.
         * For example for `Custom Post Type wizard` is filter name
         * "devhelper_cpt_wizard_form".
         *
         * @param string $output Rendered HTML.
         */
        $output = apply_filters( NG_SLUG . "_{$this->slug}_form", $output );
        echo $output;
    }

    /**
     * Render common advanced options.
     * @param boolean $display_description
     * @return string
     */
    public function render_advanced_options( $display_description ) {
        ob_start();
        include( NG_PATH . 'partials/wizard-advanced_options.phtml' );
        $output = ob_get_clean();

        /**
         * Filter wizard advanced options (HTML with part of form in <tr> element).
         *
         * Name of filter corresponds with slug of the particular wizard.
         * For example for `Custom Post Type wizard` is filter name
         * "devhelper_cpt_wizard_advanced_options_form".
         *
         * @param string $output Rendered HTML.
         */
        return apply_filters( NG_SLUG . "_{$this->slug}_advanced_options_form", $output );
    }

    /**
     * Render wizard form submit buttons.
     * @return string
     */
    public function render_submit_buttons() {
        ob_start();
        include( NG_PATH . 'partials/wizard-submit_buttons.phtml' );
        $output = ob_get_clean();

        /**
         * Filter wizard form submit buttons.
         *
         * Name of filter corresponds with slug of the particular wizard.
         * For example for `Custom Post Type wizard` is filter name
         * "devhelper_cpt_wizard_form_submit_buttons".
         *
         * @param string $output Rendered HTML.
         */
        return apply_filters( NG_SLUG . "_{$this->slug}_form_submit_buttons", $output );
    }

}

endif;
