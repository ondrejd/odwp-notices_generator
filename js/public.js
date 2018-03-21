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
  var hidden_parts = new Object();

  // Functions for showing/hiding dialog
  var showDlg = function( id ) {
    jQuery( id ).show();
  };

  var hideDlg = function( id ) {
    jQuery( id ).hide();
  };

  // Functions for turning preview mode on/off
  var previewOff = function() {
    jQuery( ".notices-generator form fieldset legend").show();
    jQuery( ".notices-generator form fieldset .inline-editor" ).css( "border-color", "#999" );
    jQuery( ".notices-generator form .description " ).show();
    jQuery( ".notices-generator #ngform_sec4" ).show();
    jQuery( ".notices-generator form fieldset .notice-survivors--form" ).show();
    jQuery( ".inline-editor--body" ).attr( "contenteditable", "true" );
    jQuery( "#odwpng-button_print" ).removeClass( "button-disabled" );
    jQuery( "#odwpng-button_borders" ).removeClass( "button-disabled" );
    jQuery( ".notices-generator .notice-image--inner" ).css( "border", "1px dashed #999" );
  };

  var previewOn = function() {
    jQuery( ".notices-generator form fieldset legend").hide();
    jQuery( ".notices-generator form fieldset .inline-editor" ).css( "border-color", "transparent" );
    jQuery( ".notices-generator form .description " ).hide();
    jQuery( "#ngform_sec4" ).hide();
    jQuery( ".notices-generator form fieldset .notice-survivors--form" ).hide();
    jQuery( ".inline-editor--body" ).removeAttr( "contenteditable" );
    jQuery( "#odwpng-button_print" ).addClass( "button-disabled" );
    jQuery( "#odwpng-button_borders" ).addClass( "button-disabled" );
    jQuery( ".notices-generator .notice-image--inner" ).css( "border", "0px none" );

    // survivors (get count of columns, create array of survivers, than render array)
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
  /* Open dialog */
  jQuery( "#selected_notice_img" ).click( function( e ) {
    if( is_preview === true ) {
      return;
    }
    shown_dialog = "#notice-images-dlg";
    showDlg( shown_dialog );
  } );
  /* Close dialog */
  jQuery( "#notice-images-dlg .button-cancel" ).click( function( e ) {
    hideDlg( "#notice-images-dlg" );
    shown_dialog = null;
  } );
  /* Pick image and close dialog */
  jQuery( "#notice-images-dlg .images-item--cont" ).click( function( e ) {
    var image = jQuery( this ).find( "img" ).attr( "src" );
    jQuery( "#selected_notice_img" ).attr( "src", image );
    hideDlg( "#notice-images-dlg" );
    shown_dialog = null;
  } );

  /**
   * Verses dialog
   */
  /* Open dialog */
  jQuery( ".notice-verses-dlg-link" ).click( function( e ) {
    if( is_preview === true ) {
      return;
    }
    shown_dialog = "#notice-verses-dlg";
    showDlg( shown_dialog );
  } );
  /* Close dialog */
  jQuery( "#notice-verses-dlg .button-cancel" ).click( function( e ) {
    hideDlg( "#notice-verses-dlg" );
    shown_dialog = null;
  } );
  /* Pick verse and close dialog */
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

  /* Change uploaded photo */
  form.on( "click", ".btn-change-image", function() {
    photo_notice.empty().hide();
    photo_file.val( "" ).show();
    photo_id.val( "" );
    photo_preview.empty().hide();
  } );

  /* Change uploaded photo */
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
   * Submit button
   */
  jQuery( "#odwpng-button_print" ).click( function() {
    console.log( "XXX Button is submitted!" );
    //...
  } );

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

  var getPartByFieldset = function( jqelm ) {
    switch( jqelm.attr( "id" ) ) {
      case "ngform_sec2"  : return "text";
      case "ngform_sec3"  : return "deceased";
      case "ngform_sec4"  : return "photo";
      case "ngform_sec5"  : return "death_date";
      case "ngform_sec6"  : return "location";
      case "ngform_sec7"  : return "survivors_text";
      case "ngform_sec8"  : return "survivors";
      case "ngform_sec9"  : return "thanks";
      case "ngform_sec10" : return "address";
      case "ngform_sec1"  : // image + verse
      default: return "unknown";
    }
  }

  /**
   * Hide section links
   */
  jQuery( ".disable-part-link" ).click( function( e ) {
    e.preventDefault();

    var elm = jQuery( this ),
        cls = "disable-part-link--toggled",
        part = getPartByFieldset( elm.parent().parent() );
    
    if( elm.hasClass( cls ) ) {
      elm.html( odwpng.msg06 );
      elm.parent().siblings().show();
      elm.removeClass( cls );
      
      if( part != "unknown" ) {
        delete hidden_parts[part];
      }
    } else {
      elm.html( odwpng.msg05 );
      elm.parent().siblings().hide();
      elm.addClass( cls );

      if( part != "unknown" ) {
        hidden_parts[part] = true;
      }
    }
  } );

  /**
   * Switch from one column to two columns.
   */
  jQuery( "input[name=ng-survivors-columns]" ).click( function() {
    var val = jQuery( "input[name=ng-survivors-columns]:checked", form ).val(),
        editor1 = jQuery( "#notice-survivors-editor" ),
        editor2 = jQuery( "#notice-survivors-editor-2" );
    
    if( val == "1" ) {
      // adjust panels
      jQuery( "#ng-survivors-panel-2" ).hide();
      jQuery( "#ng-survivors-panel-1" ).removeClass( "panel-left" );
      // set editors content
      var arr1 = editor1.html().split( "<br>" );
      var arr2 = editor2.html().split( "<br>" );
      var arr = arr1.concat( arr2 );
      editor1.html( arr.join( "<br>" ) );
    } else {
      // adjust panels
      jQuery( "#ng-survivors-panel-1" ).addClass( "panel-left" );
      jQuery( "#ng-survivors-panel-2" ).show();
      // set editors content
      var arr = editor1.html().split( "<br>" );
      var len = arr.length;
      var half = Math.ceil( len / 2 );
      var arr1 = arr.slice( 0, half );
      var arr2 = arr.slice( half, len );
      editor1.html( arr1.join( "<br>" ) );
      editor2.html( arr2.join( "<br>" ) );
    }
  } );
} );
