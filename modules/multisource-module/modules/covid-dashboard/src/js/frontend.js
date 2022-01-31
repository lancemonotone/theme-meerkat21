"use strict";
import Siema from '../../../../../../assets/src/js/vendor/siema.min.js';
import addArrows from '../../../../../../assets/src/js/vendor/siema.lib.js';

(function ( $ ) {
  let intFrameWidth = window.innerWidth;

  const studentGroup = new Siema( {
    selector: '.__MS_Student_Groups__',
    loop: true,
    draggable: true,
    duration: 500,
    perPage: {
      0: 1,
      710: 3,
      910: 4,
    },
  } );

  studentGroup.addArrows();

})( jQuery );
