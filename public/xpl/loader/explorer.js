"use strict";

var explorerList = [];
var explorerListHash = {};

class Explorer {
  constructor(opts) {
    this._DEFAULT_DATA = {
      iframeSrc: "finder.html",
      iframeId: "",
      injectionCssUrl: "loader/injection.css",
      container: window,
      override: false,
      popup: true,
      customAttr: {},
      mode: {
        multiple: true
      }
    }
    this._data = this._DEFAULT_DATA;
    this._callbacks = {};
    this.init(opts);
  }

  _generateIframeId() {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    do {
      for (var i = 0; i < 5; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
      }
    } while(explorerListHash[text])

    return text;
  }

  _getSrc() {
    try {
      var mode = JSON.stringify(this._data.mode);
    } catch (e) {
      console.log(e);
    }

    return this._data.iframeSrc + (this._data.iframeId ? "?iframeId=" + this._data.iframeId : "") + (mode ? "&mode=" + mode : "");
  }

  _insertCss(document, css) {
    var A = document.createElement("style");
    A.type = "text/css",
      A.setAttribute("explorer-style", true),
      A.innerHTML = css,
      document.getElementsByTagName("head")[0].appendChild(A)
  }

  _bindEvent(element, eventName, eventHandler) {
    if (element.addEventListener) {
      element.addEventListener(eventName, eventHandler, false);
    } else if (element.attachEvent) {
      element.attachEvent('on' + eventName, eventHandler);
    }
  }

  _listenEvent(callback) {
    this._bindEvent(window, 'message', function(e) {
      callback(e);
    });
  }

  _sendMessage(event, data) {
    var msg = JSON.stringify({
      iframeId: this._data.iframeId,
      event: event,
      data: data
    });
    $(this.getSelector("iframe")).get(0).contentWindow.postMessage(msg, '*');
  }

  getSelector(selector) {
    return (this._data.container == window ? "" : this._data.container + " ") + ".xpl-overlay[data-id='" + this._data.iframeId + "']" + (selector ? " " + selector : "");
  }

  setCustomAttr(attr, force) {
    if (force) {
      for (var key in this._data.customAttr) {
        $(this.getSelector()).removeAttr(key);
      }
      this._data.customAttr = attr;
    } else {
      this._data.customAttr = Object.assign(this._data.customAttr, attr);
    }

    for (var key in this._data.customAttr) {
      $(this.getSelector()).attr(key, this._data.customAttr[key]);
    }
  }

  setMode(mode, force) {
    if (force) {
      this._data.mode = mode;
    } else {
      this._data.mode = Object.assign(this._data.mode, mode);
    }

    this._sendMessage("MODE", this._data.mode);
  }

  clearSelectedFile() {
    this._sendMessage("CLEAR SELECTED FILE");
  }

  removeCustomAttr(key) {
    $(this.getSelector()).removeAttr(key);
    var cloneCustomAttr = this._data.customAttr && JSON.parse(JSON.stringify(this._data.customAttr));
    this._data.customAttr = {};
    for (var k in cloneCustomAttr) {
      if (k != key) {
        this._data.customAttr[k] = cloneCustomAttr[k];
      }
    }
  }

  hasAttr(key, value) {
    var attr = $(this.getSelector()).attr(key);

    if (typeof attr === typeof undefined || attr === false) {
      return false;
    }

    if (arguments.length == 2) {
      if (attr != value) {
        return false;
      }
    }

    return true;
  }

  getMode(key) {
    return key ? this._data.mode[key] : this._data.mode;
  }

  open() {
    if ($(this.getSelector()).length) {
      this.clearSelectedFile();
      $(this.getSelector()).show();
    }
  }

  close() {
    $(this.getSelector()).hide();
  }

  remove() {
    explorerList = explorerList.filter(function(el) {
      return el._data.iframeId != this._data.iframeId;
    }.bind(this));
    if (explorerListHash[this._data.iframeId]) {
      explorerListHash[this._data.iframeId] = undefined;
    }
    this._callbacks = {};
    this._data = this._DEFAULT_DATA;
    $(this.getSelector()).remove();
  }

  registerHandler(event, cb) {
    if (!this._callbacks[event]) {
      this._callbacks[event] = [];
    }
    this._callbacks[event].push(cb);
  }

  init(opts) {
    this.remove();
    this._data = Object.assign(this._data, opts);
    this._data.iframeId = this._data.iframeId ? this._data.iframeId : this._generateIframeId();
    explorerList.push(this);
    explorerListHash[this._data.iframeId] = this;

    $.get(this._data.injectionCssUrl, function(res) {
      this._insertCss(document, res);
    }.bind(this));

    $(document).on('click', '.xpl-close-lightbox', function() {
      $(this.getSelector()).hide();
    }.bind(this));

    $('.xpl-close-lightbox').parents(this.getSelector()).hide();

    // create DOM structure
    var e = document.createElement("iframe");
    e.style.display = "none",
      e.setAttribute("data-id", this._data.iframeId),
      e.width = "100%",
      e.height = "100%",
      e.src = this._getSrc(),
      e.name = "xpl-frame-" + this._data.iframeId,
      e.id = "xpl-frame-" + this._data.iframeId;

    var n = function() {
      e.removeEventListener ? e.removeEventListener("load", null, true) : e.detachEvent && e.detachEvent("onload", null),
        e.style.display = "block",
        $(this.getSelector(".xpl-loading"))[0].style.display = "none"
    }.bind(this);

    var t = '<div class="xpl-overlay animated fadeIn" data-id="' + this._data.iframeId + '" style="display: none;"><span class="xpl-close-lightbox">×</span><div class="xpl-lightbox"><div class="xpl-loading"><div class="inner-circles-loader large loading-icon"></div></div></div></div>';

    if (this._data.container && this._data.container != window) {
      if (this._data.override) {
        $(this._data.container).html(t);
      } else {
        $(this._data.container).append(t);
      }
    } else {
      document.body.insertAdjacentHTML("beforeend", t);
    }

    $(this.getSelector(".xpl-lightbox"))[0].appendChild(e);
    n();

    if (!this._data.popup) {
      $(this.getSelector()).css({
        "position": "relative",
        "height": "100%"
      });
      $(this.getSelector(".xpl-lightbox")).css({
        "position": "relative",
        "top": "0px",
        "bottom": "0px",
        "left": "0px",
        "right": "0px",
        "height": "100%"
      });
      $(this.getSelector(".xpl-close-lightbox")).css("display", "none");
    }

    this.setCustomAttr(this._data.customAttr);

    this.setMode(this._data.mode);

    // set post message listener
    this._listenEvent(function(msg) {
      var payload = {};
      try {
        payload = JSON.parse(msg.data);
      } catch (e) {
        console.log(e);
      }
      var data = payload.data || {};
      var iframeId = payload.iframeId || "";
      var event = payload.event || '__NOTHING__';

      if (iframeId && iframeId == this._data.iframeId) {
        if (event == 'READY') {
          console.log("READY");
          $(this.getSelector(".xpl-loading")).hide()
        }

        if (this._callbacks[event] && this._callbacks[event].length) {
          for (var cb of this._callbacks[event]) {
            cb(data);
          }
        }
      }
    }.bind(this));
  }
}
