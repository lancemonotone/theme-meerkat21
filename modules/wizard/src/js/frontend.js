jQuery( document ).ready( function ( $ ) {

  "use strict";

  $( '.wizard-btn' ).on( 'click', function ( e ) {
    selectElement( this );
    revealChildren( $( this ).data( 'id' ) );
  } );

  function selectElement( el ) {
    //
    $( el ).addClass( 'selected-btn' );
    $( el ).attr( 'aria-current', 'true' );
    $( el ).attr( 'aria-checked', 'true' );
    //
    $( el ).siblings().removeClass( 'selected-btn' );
    $( el ).siblings().attr( 'aria-current', 'false' );
    $( el ).siblings().attr( 'aria-checked', 'false' );
    //
    $( el ).parent().attr( 'aria-current', 'false' );
    $( el ).parent().nextAll().children().removeClass( 'selected-btn' );
    $( el ).parent().nextAll().children().attr( 'tabindex', -1 );
    $( el ).parent().nextAll().addClass( 'hidden' );
  }

  function revealChildren( rowID ) {
    var row = '#wizard-' + rowID;
    $( row ).removeClass( 'hidden' );
    $( row ).attr( 'aria-current', 'step' );
    $( row ).attr( 'aria-disabled', 'false' );
    $( row ).children().attr( 'tabindex', 0 );
    if ( $( row ).data( 'posttype' ) && $( row ).data( 'retrieved' ) === 0 ) {
      loadWizardResults( row );
      $( row ).data( 'retrieved', 1 );
    }
    //$(row).children().first().focus();
    $( row ).focus();
  }

  function loadWizardResults( row ) {
    var url = '/wp-json/wp/v2/' + $( row ).data( 'posttype' ) + '/' + $( row ).data( 'id' );
    $( row ).prepend( '<div class="wizard-spinner"><span class="bts bt-spinner bt-pulse"></span></div>' );
    $.ajax(
      {
        url: url,
        success: function ( data ) {
          $( row ).find( '.wizard-spinner' ).hide();
          $( row ).prepend( '<h2>' + data.title.rendered + '</h2>' + data.content.rendered );
        },
        error: function () {
          $( row ).prepend( '<div>Visit the URL below</div>' );
          $( row ).find( '.wizard-spinner' ).hide();
        }
      }
    );
  }

  const KEYCODE = {
    SPACE: 32,
    LEFT: 37,
    UP: 38,
    RIGHT: 39,
    DOWN: 40
  };
  $( '.chooser-wizard' ).find( '.btn' ).on( 'keydown', function ( e ) {
    if ( e.keyCode === KEYCODE.SPACE || (e.keyCode >= KEYCODE.LEFT && e.keyCode <= KEYCODE.DOWN) ) {
      console.log( 'doo something' );
      if ( e.keyCode === KEYCODE.DOWN || e.keyCode >= KEYCODE.RIGHT ) {
        if ( $( this ).next().length ) {
          $( this ).next().focus();
        } else {
          $( this ).siblings().first().focus();
          ms
        }
      } else if ( e.keyCode === KEYCODE.UP || e.keyCode >= KEYCODE.LEFT ) {
        if ( $( this ).prev().length ) {
          $( this ).prev().focus();
        } else {
          $( this ).siblings().last().focus();
        }
      } else if ( e.keyCode === KEYCODE.SPACE ) {
        selectElement( this );
      }
    }
  } );

} );

