"use strict";
import DomManipulate from '../../../../assets/src/js/dom-manipulate.js';

class ImgSwap {
  constructor( viewer ) {
    this.activeFilter = viewer.querySelector( '.curImage' );
    this.mainImage = viewer.querySelector( '.bigpic img' );
    this.mainImageSource = viewer.querySelector( '.bigpic source' );
    this.initListen();
  }

  initListen() {
    document.addEventListener( 'mouseover', function ( event ) {
      if ( event.target.matches( '.filters-bar .filter' ) ) {
        filterImg( event );
      }
    }, false );

    document.addEventListener( 'touchenter', function ( event ) {
      if ( event.target.matches( '.filters-bar .filter' ) ) {
        filterImg( event );
      }
    }, false );

    var filterImg = ( e ) => {
      if ( this.activeFilter != e.target ) {
        this.activeFilter.classList.remove( 'curImage' );
        e.type === "click" ? e.target.classList.add( 'curImage' ) : e.target.classList.add( 'curImage' );
        this.activeFilter = e.target;
        this.updateViewer( e );
      }
    }
  }

  updateViewer( e ) {
    this.mainImage.src = e.target.getAttribute( 'data-img' );
    // @todo maintain srcset twig when replacing
    this.mainImageSource.srcset = e.target.getAttribute( 'data-img' );
    this.mainImage.alt = e.target.innerText + " chart(s)";
    var colour = e.target.getAttribute( 'data-color' );
    let root = document.documentElement;
    if ( colour ) {
      root.style.setProperty( '--career-path-btn-bkg', `rgba(${colour}, .3)` );
      root.style.setProperty( '--career-path-btn-hover', `rgba(${colour}, 1)` );
    }
  }
}

let dom = new DomManipulate( ".filters-bar" );
dom.onReady( () => {
  const filterwrap = document.getElementById( "filter-swap" );
  const viewer = new ImgSwap( filterwrap );
} );