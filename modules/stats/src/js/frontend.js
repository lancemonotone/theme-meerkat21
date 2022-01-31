import Siema from '../../../../assets/src/js/vendor/siema.min.js';
import addArrows from '../../../../assets/src/js/vendor/siema.lib.js';
//doc ready
document.addEventListener( "DOMContentLoaded", function () {

  const wmsStatsSiemas = document.querySelectorAll( '.wmsstats' );
 
  for ( const siema of wmsStatsSiemas ) {
    let intFrameWidth = window.innerWidth;
    const mySiemaLength = siema.querySelectorAll('.stat').length >= 5 ? 5 :  siema.querySelectorAll('.stat').length;   
      //disable draggable > 910
      const  instance = new Siema( {
        selector: siema,
        loop: true,
        draggable: intFrameWidth <= 910,
        duration: 500,
        perPage: {
          0: 1,
          710: 3,
          910: mySiemaLength,
        },
      } );
      instance.addArrows();
   
    
    // window.addEventListener('resize', () => instance.addArrows());
    var resizeId;
    window.addEventListener('resize', () => 
      {
      
        //if (instance){
          //reinit once at the end
          // clearTimeout(resizeId);
          // resizeId = setTimeout(doneResizing(instance), 500);
          // function doneResizing(curinstance){
          //   console.log('Done resizing');
          //   curinstance.destroy(true);
          //   curinstance.init();
          // }
        //}

      }
  
    );

  }
 

} );
