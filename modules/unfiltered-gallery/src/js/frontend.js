"use strict";

//doc ready
document.addEventListener( "DOMContentLoaded", function () {

  // check if there is an open button or link
  let elo = document.querySelector( '.ug_open' );
  if ( elo ) {
    // call wmsDoOverlay on the ug gallery from featherlight-config in lib
    jQuery('.ug a ').wmsDoOverlay();
  
    elo.onclick = function ( e ) {
      e.preventDefault();
  
      // click the first one and open featherlight
      document.querySelector('.ug a').click();

     }
    }

} );
