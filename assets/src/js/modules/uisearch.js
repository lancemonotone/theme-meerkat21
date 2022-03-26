'use strict';

function UISearch( el ) {
  if ( this.el = el || null ) {
    this.inputEl = el.querySelector( 'form > input.wms-navbox-input' );
    this.goBtn = el.querySelector( 'form > input.wms-navbox-button' );
    this.cancelBtn = el.querySelector( 'form > div.wms-navbox-cancel' );
    this.searchBtn = document.querySelector( '#wms-search-btn button' );
    this._initEvents();
  }
}

UISearch.prototype = {
  _initEvents: function () {
    var self         = this,
        initSearchFn = function ( ev ) {
          //this is used to show a loading icon if the search button is pressed and results aren't available
          // (usually due to a poor connection)
          jQuery( "#wms-search-btn" ).addClass( "clicked" );
          ev.stopPropagation();

          // trim its value, and toggle
          self.inputEl.value = self.inputEl.value.trim();
          if ( !jQuery( self.el ).hasClass( 'wms-search-open' ) ) { //open
            ev.preventDefault();
            self.open();
          } else if ( jQuery( self.el ).hasClass( 'wms-search-open' ) ) { // close
            ev.preventDefault();
            self.close();
          }
        }

    self.searchBtn.addEventListener( 'click', initSearchFn );
    self.searchBtn.addEventListener( 'touchstart', initSearchFn );

    //stop propagation to avoid bodyFn from closing
    var exemptObj = [self.inputEl, self.goBtn, self.cancelBtn]
    exemptObj.forEach( item => {
      item.addEventListener( 'click', function ( ev ) {
        ev.stopPropagation();
      } );
      item.addEventListener( 'touchstart', function ( ev ) {
        ev.stopPropagation();
      } );
    } )
    //close on escape key
    jQuery( document ).keyup( function ( e ) {
      if ( e.keyCode == 27 ) { // escape key maps to keycode `27`
        self.close();
      }
    } );

  },
  open: function () {
    var self = this;

    jQuery( self.el ).addClass( 'wms-search-open' );
    // focus the input
    self.inputEl.select();
    self.inputEl.focus();
    //this is a non-optimal solution to bring focus on screen readers
    setTimeout( function () {
      self.inputEl.focus();
    }, 300 );

    self.searchBtn.setAttribute( 'aria-expanded', true );

    // close the search input if body is clicked
    var bodyFn = function ( ev ) {
      self.close();
      this.removeEventListener( 'click', bodyFn );
      this.removeEventListener( 'touchstart', bodyFn );
    };
    document.addEventListener( 'click', bodyFn );
    document.addEventListener( 'touchstart', bodyFn );
  },
  close: function () {
    var self = this;
    jQuery( self.searchBtn ).removeClass( "clicked" );
    self.searchBtn.setAttribute( 'aria-expanded', false );
    this.inputEl.blur();
    jQuery( self.el ).attr( 'aria-expanded' ) != 'true' ? 'true' : 'false';
    jQuery( self.el ).removeClass( 'wms-search-open' );
  }

}

document.addEventListener( "DOMContentLoaded", function () {
  new UISearch( document.getElementById( 'wms-navbox-wrap' ) );
} );
