//add a click event for the purge assets btn in the admin bar
jQuery( '#wp-admin-bar-{%action%} .ab-item' ).on( "click", function ( e ) {
  e.preventDefault;

  var data = {
    action: '{%action%}',
    security: '{%nonce%}'
  };

  $.post(
    MyAjax.ajaxurl,
    data,
    function ( response ) {
      //alert( response );
      location.reload( true );
    } );
} );