"use strict";

import Siema from '../../../../assets/src/js/vendor/siema.min.js';
import addArrows from '../../../../assets/src/js/vendor/siema.lib.js';

//doc ready
document.addEventListener( "DOMContentLoaded", function () {

  let svSelector = document.getElementById( 'simplevideos' );
  let svSiema;
  if ( !svSiema && svSelector ) {

    svSiema = new Siema( {
      selector: '#simplevideos',
      loop: true,
      duration: 500,
      perPage: {
        0: 3,
      },
      onChange: () => updateVisible(),
    } );
    svSiema.addArrows();
  }
  const updateVisible = () => {

    let videoViewport = document.getElementById( 'simplevideos' );

    var viewportOffset = videoViewport.getBoundingClientRect();
    // these are relative to the viewport, i.e. the window
    var l = viewportOffset.left;
    var r = viewportOffset.left + videoViewport.offsetWidth;

    let elementList = videoViewport.querySelectorAll( ".simple-video" );
    const c = videoViewport.childNodes[0];
    c.addEventListener( 'transitionend', () => {
      let adds = 0;
      elementList.forEach( ( slide, i ) => {

        var slideViewportOffset = slide.getBoundingClientRect();
        //chrome and firefox are off by less than a px on this value
        let addOrRemove = (l <= slideViewportOffset.left + 1 && slideViewportOffset.left - 1 <= r) ? 'add' : 'remove';

        if ( addOrRemove == 'add' ) {
          adds++;
        }
        addOrRemove = adds == 2 ? 'add' : 'remove';
        slide.classList[addOrRemove]( 'expand' );

      } )

    } );

  };
} );

