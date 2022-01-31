"use strict";

(function () {
  //remove parent
  const removeClosest = function ( elem, selector ) {
    try {
      const parent = elem.closest( selector );
      document.body.classList.remove( "alert-open" );
      // parent.parentNode.removeChild(parent);
    }
    catch ( e ) {
      //setup if message has changed
      setup_click();

      return false;
    }
  };

  //function to check if localstorage is supported
  const supports_storage = function ( e ) {
    try {
      return 'localStorage' in window && window['localStorage'] !== null;
    }
    catch ( e ) {
      return false;
    }
  };

  //remove emergency msg or add message for non-support
  const dont_show_alert = function ( hide_id ) {
    /* Set localstorage to hide this item */
    if ( supports_storage() ) {
      localStorage.setItem( 'wms_noalerts', hide_id );
    }

  };

  //click event
  const setup_click = function () {
    if ( document.getElementById( 'alert-close' ) ) {

      const job1 = function () {
        return new Promise( function ( resolve, reject ) {
          setTimeout( function () {
            resolve( 'result of job 1' );
          }, 1000 );
        } );
      };

      const job2 = function () {
        return new Promise( function ( resolve, reject ) {
          setTimeout( function () {
            resolve( 'result of job 2' );
          }, 1000 );
        } );
      };

      const promise = job1();

      promise

        .then( function () {
          //setup click event
          document.getElementById( 'alert-close' ).addEventListener( 'click', function () {
            console.log( 'click' );
            const wrapper = event.target.closest( '#alert-close' );

            const hide_node = wrapper.getAttribute( "data-alert" );
            dont_show_alert( hide_node );

            removeClosest( event.target, '.fl-builder-content' );

          } );

          return job2();
        } )

        .then( function () {
          //make visible
          document.body.classList.add( "alert-open" );
        } )
    }
  };

  //if user dismissed, remove for this session
  if ( localStorage.getItem( 'wms_noalerts' ) > 0 ) {

    const remove_id = localStorage.getItem( 'wms_noalerts' );
    const remove_item = document.querySelector( `[data-alert="${remove_id}"]` );

    removeClosest( remove_item, '.fl-builder-content' )

  } else {
    setup_click();

  }
})();