var DomManipulate = function (selector) {
  document.addEventListener("DOMContentLoaded", () => {
    this.element = document.querySelector(selector);

    if (typeof this.callback === "function") this.callback();
  });
};

DomManipulate.prototype.onReady = function (callback) {
  this.callback = callback;
};

DomManipulate.prototype.getElement = function () {
  return this.element;
};

DomManipulate.prototype.write = function (text) {
  return (this.element.innerText = text);
};

DomManipulate.prototype.getChildren = function (selectors) {
  let elementList = this.element.querySelectorAll(selectors);
  return elementList;
};

/**
 * Check if an element is out of the viewport
 * @param  {Node}  elem The element to check
 * @return {Object}     A set of booleans for each side of the element
 */
DomManipulate.isOutOfViewport = function (elem) {
  // Get element's bounding
  var bounding = elem.getBoundingClientRect();

  // Check if it's out of the viewport on each side
  var out = {};
  out.top = bounding.top < 0;
  out.left = bounding.left < 0;
  out.bottom =
    bounding.bottom >
    (window.innerHeight || document.documentElement.clientHeight);
  out.right =
    bounding.right >
    (window.innerWidth || document.documentElement.clientWidth);
  out.any = out.top || out.left || out.bottom || out.right;
  out.x = out.left || out.right;
  //   out.all = out.top && out.left && out.bottom && out.right;
  return out;
};

DomManipulate.prototype.markOutOfViewport = function (selectors) {
  let elementList = this.element.querySelectorAll(selectors);
  elementList.forEach(function (item) {
    var isOut = DomManipulate.isOutOfViewport(item);
    item.tabIndex = "0";
    // item.style.outline = "#f00 solid 10px"; //debug
    if (isOut.x) {
      // item.style.outline = "#4CAF50 solid 10px"; //debug
      item.tabIndex = "-1";
    }
  });
  return elementList;
};

export { DomManipulate as default };
