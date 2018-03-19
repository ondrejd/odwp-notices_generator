jQuery( document ).ready( function() {

    // Functions for showing/hiding dialog
    var showDlg = function( id ) { jQuery( id ).show(); window.scrollTo( 0, 0 ); };
    var hideDlg = function( id ) { jQuery( id ).hide(); };

    /**
     * Images dialog
     */
    // 1) Open dialog
    jQuery( ".notice-images-dlg-link,#selected_notice_img" ).click( function( e ) {
        showDlg( "#notice-images-dlg" );
    } );
    // 2) Close dialog
    jQuery( "#notice-images-dlg .button-cancel" ).click( function( e ) {
        hideDlg( "#notice-images-dlg" );
    } );
    // 3) Pick image and close dialog
    jQuery( "#notice-images-dlg .images-item--cont" ).click( function( e ) {
        var image = jQuery( this ).find( "img" ).attr( "src" );
        jQuery( "#selected_notice_img" ).attr( "src", image );
        hideDlg( "#notice-images-dlg" );
    } );

    /**
     * Verses dialog
     */
    // 1) Open dialog
    jQuery( ".notice-verses-dlg-link" ).click( function( e ) {
        showDlg( "#notice-verses-dlg" );
    } );
    // 2) Close dialog
    jQuery( "#notice-verses-dlg .button-cancel" ).click( function( e ) {
        hideDlg( "#notice-verses-dlg" );
    } );
    // 3) Pick verse and close dialog
    jQuery( "#notice-verses-dlg .verses-list li" ).click( function( e ) {
        var verse = jQuery( this ).html();
        jQuery( "#notice-verse-editor" ).html( verse );
        hideDlg( "#notice-verses-dlg" );
    } );

    /**
     * Borders dialog
     */
    // 1) Open dialog
    jQuery( "#odwpng-button_borders" ).click( function( e ) {
        showDlg( "#notice-borders-dlg" );
    } );
    // 2) Close dialog
    jQuery( "#notice-borders-dlg .button-cancel" ).click( function( e ) {
        hideDlg( "#notice-borders-dlg" );
    } );
    // 3) Pick border and close dialog
    jQuery( "#notice-borders-dlg .borders-item" ).click( function( e ) {
        var border = jQuery( this ).text();
        for( var i = 1; i < 5; i++ ) {
            jQuery( ".notices-generator--cont" ).removeClass( "border-0" + i );
        }
        jQuery( ".notices-generator--cont" ).addClass( "border-" + border );
        jQuery( "#odwpng-notice_border" ).val( "border" + border );
        hideDlg( "#notice-borders-dlg" );
    } );
} );
