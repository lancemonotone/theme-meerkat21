/**
 * This file is loaded when the settings panel for your module is loaded:
 * https://kb.wpbeaverbuilder.com/article/599-cmdg-04-module-settings-css-and-javascript
 */
(function ( $ ) {
  "use strict";

  FLBuilder.registerModuleHelper( 'boilerplate', {

    init: function () {
      // This is just an example. Replace with your code.
      var form    = $( '.fl-builder-settings' ),
          spacing = form.find( 'input[name=photo_spacing]' );

      // Note the add_action()-like callback.
      spacing.on( 'input', this._previewSpacing );
    },

    // Callback. Just an example. Replace with your code.
    _previewSpacing: function ( e ) {
      var preview = FLBuilder.preview,
          form    = $( '.fl-builder-settings' ),
          layout  = form.find( 'select[name=layout]' ).val(),
          spacing = form.find( 'input[name=photo_spacing]' ).val();

      if ( 'collage' === layout ) {
        spacing = '' === spacing ? 0 : spacing;
        preview.updateCSSRule( preview.classes.node + ' .fl-mosaicflow', 'margin-left', '-' + spacing + 'px' );
        preview.updateCSSRule( preview.classes.node + ' .fl-mosaicflow-item', 'margin', '0 0 ' + spacing + 'px ' + spacing + 'px' );
      } else {
        preview.delayPreview( e );
      }
    },
  } );

})( jQuery );
