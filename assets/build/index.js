!function(){"use strict";var e={219:function(){function e(e){(this.el=e||null)&&(this.inputEl=e.querySelector("form > input.wms-navbox-input"),this.goBtn=e.querySelector("form > input.wms-navbox-button"),this.cancelBtn=e.querySelector("form > div.wms-navbox-cancel"),this.searchBtn=document.querySelector("#wms-search-btn button"),this._initEvents())}e.prototype={_initEvents:function(){var e=this,t=function(t){jQuery("#wms-search-btn").addClass("clicked"),t.stopPropagation(),e.inputEl.value=e.inputEl.value.trim(),jQuery(e.el).hasClass("wms-search-open")?jQuery(e.el).hasClass("wms-search-open")&&(t.preventDefault(),e.close()):(t.preventDefault(),e.open())};e.searchBtn.addEventListener("click",t),e.searchBtn.addEventListener("touchstart",t),[e.inputEl,e.goBtn,e.cancelBtn].forEach((e=>{e.addEventListener("click",(function(e){e.stopPropagation()})),e.addEventListener("touchstart",(function(e){e.stopPropagation()}))})),jQuery(document).keyup((function(t){27==t.keyCode&&e.close()}))},open:function(){var e=this;jQuery(e.el).addClass("wms-search-open"),e.inputEl.select(),e.inputEl.focus(),setTimeout((function(){e.inputEl.focus()}),300),e.searchBtn.setAttribute("aria-expanded",!0);var t=function(n){e.close(),this.removeEventListener("click",t),this.removeEventListener("touchstart",t)};document.addEventListener("click",t),document.addEventListener("touchstart",t)},close:function(){var e=this;jQuery(e.searchBtn).removeClass("clicked"),e.searchBtn.setAttribute("aria-expanded",!1),this.inputEl.blur(),jQuery(e.el).attr("aria-expanded"),jQuery(e.el).removeClass("wms-search-open")}},document.addEventListener("DOMContentLoaded",(function(){new e(document.getElementById("wms-navbox-wrap"))}))}},t={};function n(s){var r=t[s];if(void 0!==r)return r.exports;var o=t[s]={exports:{}};return e[s](o,o.exports,n),o.exports}window.jQuery,n(219)}();