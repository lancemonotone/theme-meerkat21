(()=>{"use strict";var t=function(t){document.addEventListener("DOMContentLoaded",(()=>{this.element=document.querySelector(t),"function"==typeof this.callback&&this.callback()}))};function e(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,r.key,r)}}t.prototype.onReady=function(t){this.callback=t},t.prototype.getElement=function(){return this.element},t.prototype.write=function(t){return this.element.innerText=t},t.prototype.getChildren=function(t){return this.element.querySelectorAll(t)},t.isOutOfViewport=function(t){var e=t.getBoundingClientRect(),n={};return n.top=e.top<0,n.left=e.left<0,n.bottom=e.bottom>(window.innerHeight||document.documentElement.clientHeight),n.right=e.right>(window.innerWidth||document.documentElement.clientWidth),n.any=n.top||n.left||n.bottom||n.right,n.x=n.left||n.right,n},t.prototype.markOutOfViewport=function(e){let n=this.element.querySelectorAll(e);return n.forEach((function(e){var n=t.isOutOfViewport(e);e.tabIndex="0",n.x&&(e.tabIndex="-1")})),n};var n=function(){function t(e){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),this.activeFilter=e.querySelector(".curImage"),this.mainImage=e.querySelector(".bigpic img"),this.mainImageSource=e.querySelector(".bigpic source"),this.initListen()}var n,r;return n=t,(r=[{key:"initListen",value:function(){var t=this;document.addEventListener("mouseover",(function(t){t.target.matches(".filters-bar .filter")&&e(t)}),!1),document.addEventListener("touchenter",(function(t){t.target.matches(".filters-bar .filter")&&e(t)}),!1);var e=function(e){t.activeFilter!=e.target&&(t.activeFilter.classList.remove("curImage"),e.type,e.target.classList.add("curImage"),t.activeFilter=e.target,t.updateViewer(e))}}},{key:"updateViewer",value:function(t){this.mainImage.src=t.target.getAttribute("data-img"),this.mainImageSource.srcset=t.target.getAttribute("data-img"),this.mainImage.alt=t.target.innerText+" chart(s)";var e=t.target.getAttribute("data-color"),n=document.documentElement;e&&(n.style.setProperty("--career-path-btn-bkg","rgba(".concat(e,", .3)")),n.style.setProperty("--career-path-btn-hover","rgba(".concat(e,", 1)")))}}])&&e(n.prototype,r),t}();new t(".filters-bar").onReady((function(){var t=document.getElementById("filter-swap");new n(t)}))})();
//# sourceMappingURL=frontend.js.map