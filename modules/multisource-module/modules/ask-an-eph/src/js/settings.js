(function ( $ ) {
  if ( typeof FLBuilder === 'undefined' ) {
    return;
  }
  FLBuilder.registerModuleHelper( 'post-carousel', {

    init: function () {
      const form = $( '.fl-builder-settings' );
      const layout = form.find( 'select[name=layout]' );

      this._fixForm();
      layout.on( 'change', this._fixForm );
    },

    _fixForm: function () {
      const form = $( '.fl-builder-settings' );
      const layout = form.find( 'select[name=template]' );
      const info = form.find( 'div[id=fl-builder-settings-section-info]' );
      const imageSection = form.find( 'div[id=fl-builder-settings-section-image]' );

      if ( 'ask-an-eph' === layout.val() ) {
        info.hide();
        imageSection.hide();
      } else {
        info.show();
        imageSection.show();
      }
    }
  } );

})( jQuery );