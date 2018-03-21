/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-notices_generator for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-notices_generator
 * @since 1.O.O
 * @todo Translate strings!
 */

jQuery( document ).ready( function( $ ) {
    tinymce.create( 'tinymce.plugins.odwpng_designer_shortcode', {
        init : function( ed, url) {
            ed.addCommand( 'odwpng_insert_designer_shortcode', function() {
                tinymce.execCommand( 'mceInsertContent', false, '[notice_designer]' );
            } );
            ed.addButton( 'odwpng_designer_shortcode', {
                title : 'Vložit návrhář smutečních oznámení',
                cmd : 'odwpng_insert_designer_shortcode',
                image: url + '/../img/tinymce-button-32x32.png'
            } );
        },
    });

    tinymce.PluginManager.add( 'odwpng_designer_shortcode', tinymce.plugins.odwpng_designer_shortcode );
});