"use strict";

//youtube deferred
var YTdeferred = jQuery.Deferred();
window.onYouTubeIframeAPIReady = function () {
  YTdeferred.resolve(window.YT);
};
var tag = document.createElement("script");

tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName("script")[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
//end youtube deferred

jQuery(document).ready(function ($) {
  document
    .getElementById("splash-overlay-close")
    .addEventListener("click", function () {
      jQuery(".fl-module-splash-page #splash-overlay").remove();
      resetAnimation("gif-to-reset");
    });
  //MP4
  var video = document.getElementById("splash-control");
  if (video) {
    video.onended = function (e) {
      jQuery("video")[0].autoplay = false;
      jQuery("video")[0].load();
      jQuery("#splash-overlay").remove();
      resetAnimation("gif-to-reset");
    };
  } else {
    YTdeferred.done(function (YT) {
      // use YT here
      var player;
      player = new YT.Player("splash-player", {
        events: {
          onReady: onPlayerReady,
          onStateChange: onPlayerStateChange,
        },
      });

      function onPlayerReady(event) {}
      function stateUpdate(playerStatus) {
        var color;
        if (playerStatus == -1) {
          // unstarted
        } else if (playerStatus == 0) {
          // ended
          jQuery("#splash-overlay").remove();
          resetAnimation("gif-to-reset");
        } else if (playerStatus == 1) {
          // playing
        } else if (playerStatus == 2) {
          // paused
        } else if (playerStatus == 3) {
          // buffering
        } else if (playerStatus == 5) {
          // video cued
        }
      }
      function onPlayerStateChange(event) {
        stateUpdate(event.data);
      }
      //end use YT
    });
  }
});

var resetAnimation = function resetAnimation($id) {
  var resetGif = document.getElementById($id);
  resetGif.src = resetGif.src;
};
