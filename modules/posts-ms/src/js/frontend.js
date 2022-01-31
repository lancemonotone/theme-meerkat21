"use strict";

import Siema from "../../../../assets/src/js/vendor/siema.min.js";
import addArrows from "../../../../assets/src/js/vendor/siema.lib.js";
import DomManipulate from "../../../../assets/src/js/dom-manipulate.js";

//carousel ready

let currentCarousel = new DomManipulate("#alumnistories");
currentCarousel.onReady(() => {
  let asSelector = document.getElementById("alumnistories");
  let asSiema;
  if (!asSiema && asSelector) {
    asSiema = new Siema({
      selector: "#alumnistories",
      onChange: onChangeCallback,
      loop: true,
      duration: 500,
      perPage: {
        0: 3,
        910: 4,
        1200: 5,
        1490: 6,
      },
    });
    asSiema.addArrows();
    let elList = currentCarousel.markOutOfViewport(".alumni-story a");
    function onChangeCallback() {
      setTimeout(() => {
        let elList = currentCarousel.markOutOfViewport(".alumni-story a");
      }, 500); //this matches the siema duration
    }
  }
});

// window.addEventListener("load", (event) => {
//   console.log("page is fully loaded");
// });
