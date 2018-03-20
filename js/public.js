jQuery( document ).ready( function() {

  // Helper variables
  var is_preview = false;
  var is_photo_uploaded = false;
  var shown_dialog = null;
  var form = jQuery( ".notices-generator form" );
  var form_notice = jQuery( ".notices-generator .form-notice" );
  var photo_notice  = jQuery( "#photo-upload-notice" );
  var photo_preview = jQuery( "#photo-upload-preview" );
  var photo_noimg = jQuery( "#photo-upload-noimg" );
  var photo_file = form.find( "[name=photo-upload]" );
  var photo_id = form.find( "[name=photo_id]" );

  // Functions for showing/hiding dialog
  var showDlg = function( id ) {
    jQuery( id ).show();
    window.scrollTo( 0, 0 );
  };

  var hideDlg = function( id ) {
    jQuery( id ).hide();
  };

  // Functions for turning preview mode on/off
  var previewOff = function() {
    jQuery( ".notices-generator form fieldset legend").show();
    jQuery( ".notices-generator form fieldset .inline-editor" ).css( "border-color", "#000" );
    jQuery( ".notices-generator form .description " ).show();
    jQuery( ".notices-generator #ngform_sec4" ).show();
    jQuery( ".notices-generator form fieldset .notice-survivors--form" ).show();
    jQuery( "#odwpng-button_print" ).removeClass( "button-disabled" );
    jQuery( "#odwpng-button_borders" ).removeClass( "button-disabled" );
    jQuery( "#odwpng-button_background" ).removeClass( "button-disabled" );
  };

  var previewOn = function() {
    // Schovat všechny názvy sekcí formuláře
    jQuery( ".notices-generator form fieldset legend").hide();
    /// schovat okraje editorů
    jQuery( ".notices-generator form fieldset .inline-editor" ).css( "border-color", "transparent" );
    // skryt popisky
    jQuery( ".notices-generator form .description " ).hide();
    // skrýt pole fotografie, pokud žádná není nahrána
    jQuery( "#ngform_sec4" ).hide();
    // skryjeme radioboxy u pozustalych
    jQuery( ".notices-generator form fieldset .notice-survivors--form" ).hide();
    // zakazeme tlacitka pro vyber okraju a pozadi
    jQuery( "#odwpng-button_print" ).addClass( "button-disabled" );
    jQuery( "#odwpng-button_borders" ).addClass( "button-disabled" );
    jQuery( "#odwpng-button_background" ).addClass( "button-disabled" );

    // pozustali (zjistime pocet sloupecku a vytvorime pole pozustalych, ktere vytiskneme)
    var cols_count = parseInt( jQuery( "input[name=ng-survivors-columns]:checked", ".notices-generator form" ).val() );
    var survivors_raw = jQuery( "#notice-survivors-editor" ).html().split( "," );
    var survivors = new Array();

    jQuery.each(
      jQuery( "#notice-survivors-editor" ).html().split( "," ),
      function( idx, val ){ survivors.push( ( new String( val ) ).trim() ); }
    );

    console.log( survivors );
    // XXX Finish this!!!
  };
  
  // XHR listener for photo upload
  var photo_upload_xhr_listener = function( e ) {
    console.log( "photo_upload_xhr_listener", e );
    if( e.lengthComputable ) {
      var perc = ( e.loaded / e.total ) * 100;
      perc = perc.toFixed( 2 );
      photo_notice.html( odwpng.msg02.replace( '%%DONE%%', perc ) );
    }
  };

  // Watch if ESC was pressed...
  jQuery( document ).keyup( function( e ) {
    if( e.keyCode == 27 ) {
      if( is_preview === true ) {
        previewOff();
      } else if( shown_dialog !== null ) {
        hideDlg( shown_dialog );
      }
    }
  } );

  /**
   * Images dialog
   */
  // 1) Open dialog
  jQuery( ".notice-images-dlg-link,#selected_notice_img" ).click( function( e ) {
    if( is_preview === true ) {
      return;
    }
    shown_dialog = "#notice-images-dlg";
    showDlg( shown_dialog );
  } );
  // 2) Close dialog
  jQuery( "#notice-images-dlg .button-cancel" ).click( function( e ) {
    hideDlg( "#notice-images-dlg" );
    shown_dialog = null;
  } );
  // 3) Pick image and close dialog
  jQuery( "#notice-images-dlg .images-item--cont" ).click( function( e ) {
    var image = jQuery( this ).find( "img" ).attr( "src" );
    jQuery( "#selected_notice_img" ).attr( "src", image );
    hideDlg( "#notice-images-dlg" );
    shown_dialog = null;
  } );

  /**
   * Verses dialog
   */
  // 1) Open dialog
  jQuery( ".notice-verses-dlg-link" ).click( function( e ) {
    if( is_preview === true ) {
      return;
    }
    shown_dialog = "#notice-verses-dlg";
    showDlg( shown_dialog );
  } );
  // 2) Close dialog
  jQuery( "#notice-verses-dlg .button-cancel" ).click( function( e ) {
    hideDlg( "#notice-verses-dlg" );
    shown_dialog = null;
  } );
  // 3) Pick verse and close dialog
  jQuery( "#notice-verses-dlg .verses-list li" ).click( function( e ) {
    var verse = jQuery( this ).html();
    jQuery( "#notice-verse-editor" ).html( verse );
    hideDlg( "#notice-verses-dlg" );
    shown_dialog = null;
  } );

  /**
   * Photo upload
   */

  photo_file.on( "change", function( e ) {
    e.preventDefault();

    var fdata = new FormData();
    fdata.append( "action", "upload-photo" );
    fdata.append( "photo-upload", photo_file[0].files[0] );
    fdata.append( "name", photo_file[0].files[0].name );
    fdata.append( "_wpnonce", odwpng.nonce );

    jQuery.ajax({
      url: odwpng.upload_url,
      data: fdata,
      processData: false,
      contentType: false,
      dataType: "json",
      type: "POST",
      beforeSend: function() {
        photo_file.hide();
        photo_notice.html( odwpng.msg01 );
        console.log( odwpng.msg01 );
      },
      xhr: function() {
        var my_xhr = jQuery.ajaxSettings.xhr();

        if( my_xhr.upload ) {
          my_xhr.upload.addEventListener( "progress", photo_upload_xhr_listener, false );
        } else {
          console.log( my_xhr.upload );
        }

        return my_xhr;
      },
      complete: function( my_xhr, status ) {
        if( status === "success" ) {
console.log( "complete", my_xhr, status );
        } else {
          photo_notice.html( odwpng.msg04 );
          photo_file.show();
          photo_id.val( "" );
        }
      }/*,
      success: function( response, status, my_xhr ) {
console.log( "success", my_xhr, status );
        if ( response.success ) {
          var img = jQuery( "<img>", { src: response.data.url } );
console.log( img );
          photo_notice.html( odwpng.msg03 );
          photo_id.val( response.data.id );
          photo_preview.html( img ).show();
        } else {
console.log( odwpng.msg04 );
          photo_notice.html( odwpng.msg04 );
          photo_file.show();
          photo_id.val( "" );
        }
      }*/
    } );
  } );

  form.on( "click", ".btn-change-image", function() {
    photo_notice.empty().hide();
    photo_file.val( "" ).show();
    photo_id.val( "" );
    photo_preview.empty().hide();
  } );

  photo_file.on( "click", function() {
    photo_file.val( "" );
    photo_id.val( "" );
  } );

  /**
   * Borders dialog
   */
  // 1) Open dialog
  jQuery( "#odwpng-button_borders" ).click( function() {
    if( is_preview === true ) {
      return;
    }
    showDlg( "#notice-borders-dlg" );
    shown_dialog = "#notice-borders-dlg";
  } );
  // 2) Close dialog
  jQuery( "#notice-borders-dlg .button-cancel" ).click( function() {
    hideDlg( "#notice-borders-dlg" );
    shown_dialog = null;
  } );
  // 3) Pick border and close dialog
  jQuery( "#notice-borders-dlg .borders-item" ).click( function() {
    var border = jQuery( this ).text();

    for( var i = 1; i < 5; i++ ) {
      jQuery( ".notices-generator--cont" ).removeClass( "border-0" + i );
    }

    jQuery( ".notices-generator--cont" ).addClass( "border-" + border );
    jQuery( "#odwpng-notice_border" ).val( "border" + border );
    hideDlg( "#notice-borders-dlg" );
    shown_dialog = null;
  } );

  /**
   * Background dialog
   */
  /* XXX Finish this */

  /**
   * Submit button
   */
  /* XXX Finish this */

  /**
   * Preview button
   */
  jQuery( "#odwpng-button_preview" ).click( function() {
    if( is_preview !== true ) {
      previewOn();
    } else {
      previewOff();
    }

    is_preview = !is_preview;
  } );

  /**
   * Hide section links
   */
  jQuery( ".disable-part-link" ).click( function( mouse_event ) {
    jQuery( this ).parent().siblings().toggle();
  } );
} );
