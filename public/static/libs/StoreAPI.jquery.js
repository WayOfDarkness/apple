if ((typeof StoreAPI) === 'undefined') {
  StoreAPI = {};
}

function money(num) {
  if (num) num = num.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
  return num;
}

StoreAPI.onError = function(XMLHttpRequest, textStatus) {
  var data = eval('(' + XMLHttpRequest.responseText + ')');
  if (!!data.message) {
    alert(data.message + '(' + data.status + '): ' + data.description);
  } else {
    alert('Error : ' + StoreAPI.fullMessagesFromErrors(data).join('; ') + '.');
  }
};

StoreAPI.fullMessagesFromErrors = function(errors) {
  var fullMessages = [];
  jQuery.each(errors, function(attribute, messages) {
    jQuery.each(messages, function(index, message) {
      fullMessages.push(attribute + ' ' + message);
    });
  });
  return fullMessages
}

StoreAPI.onCartUpdate = function(cart) {
  alert("Cập nhật giỏ hàng thành công");
};

StoreAPI.onItemAdded = function(line_item) {
  alert('Sản phẩm đã được thêm vào giỏ hàng');
};

StoreAPI.onOrderSuccess = function() {
  alert("Tạo đơn hàng thành công");
}

StoreAPI.onSendContact = function() {
  alert("Gửi liên hệ thành công");
}
StoreAPI.onSendSubscribe = function() {
  alert("Gửi Subscriber thành công");
}

StoreAPI.addItem = function(variant_id, quantity, callback) {
  var quantity = quantity || 1;
  var params = {
    type: 'POST',
    url: '/api/cart/add',
    data: {
      variant_id: variant_id,
      quantity: quantity
    },
    success: function(line_item) {
      if ((typeof callback) === 'function') {
        callback(line_item);
      } else {
        StoreAPI.onItemAdded(line_item);
      }
    },
    error: function(XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
};

StoreAPI.getCart = function(callback) {
  jQuery.get('/api/cart/getCart', function(cart) {
    if ((typeof callback) === 'function') {
      callback(cart);
    } else {
      StoreAPI.onCartUpdate(cart);
    }
  });
};

StoreAPI.changeItem = function(variant_id, quantity, callback) {
  var params = {
    type: 'POST',
    url: '/api/cart/change',
    data: 'quantity=' + quantity + '&variant_id=' + variant_id,
    dataType: 'json',
    success: function(cart) {
      if ((typeof callback) === 'function') {
        callback(cart);
      } else {
        StoreAPI.onCartUpdate(cart);
      }
    },
    error: function(XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
};

StoreAPI.removeItem = function(variant_id, callback) {
  var params = {
    type: 'POST',
    url: '/api/cart/change',
    data: 'quantity=0&variant_id=' + variant_id,
    dataType: 'json',
    success: function(cart) {
      if ((typeof callback) === 'function') {
        callback(cart);
      } else {
        StoreAPI.onCartUpdate(cart);
      }
    },
    error: function(XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
};

StoreAPI.clear = function(callback) {
  var params = {
    type: 'POST',
    url: '/api/cart/clear',
    data: '',
    dataType: 'json',
    success: function(cart) {
      if ((typeof callback) === 'function') {
        callback(cart);
      } else {
        StoreAPI.onCartUpdate(cart);
      }
    },
    error: function(XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
};

StoreAPI.updateCartAttributes = function(attributes, callback) {
  var data = '';
  if (jQuery.isArray(attributes)) {
    jQuery.each(attributes, function(indexInArray, valueOfElement) {
      var key = attributeToString(valueOfElement.key);
      if (key !== '') {
        data += 'attributes[' + key + ']=' + attributeToString(valueOfElement.value) + '&';
      }
    });
  } else if ((typeof attributes === 'object') && attributes !== null) {
    jQuery.each(attributes, function(key, value) {
      data += 'attributes[' + attributeToString(key) + ']=' + attributeToString(value) + '&';
    });
  }
  var params = {
    type: 'POST',
    url: '/cart/update',
    data: data,
    dataType: 'json',
    success: function(cart) {
      if ((typeof callback) === 'function') {
        callback(cart);
      } else {
        StoreAPI.onCartUpdate(cart);
      }
    },
    error: function(XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
};

StoreAPI.getSubRegion = function (region_id, callback) {
  jQuery.get('/api/region/' + region_id + '/listSubRegion', callback);
}

StoreAPI.checkout = function(data, callback) {
  var params = {
    type: 'POST',
    url: '/api/order',
    data: data,
    success: function(line_item) {
      if ((typeof callback) === 'function') {
        callback(line_item);
      } else {
        StoreAPI.onOrderSuccess();
      }
      if (!line_item.code) {
        StoreAPI.sendEmail(line_item.order_id);
        StoreAPI.sendEmailAdmin(line_item.order_id);
      }
    },
    error: function(XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.sendContact = function(data, callback) {
  var params = {
    type: 'POST',
    url: '/api/contact',
    data: data,
    success: function(line_item) {
      if ((typeof callback) === 'function') {
        callback(line_item);
      } else {
        StoreAPI.onSendContact();
      }
      if (!line_item.code) {
        StoreAPI.sendEmailContactAdmin(line_item.contact_id);
      }
    },
    error: function(XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.checkCoupon = function(data, callback) {
  var params = {
    type: 'POST',
    url: '/api/checkCoupon',
    data: data,
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.sendEmail = function(orderID) {
  var params = {
    type: 'GET',
    url: '/api/order/' + orderID + '/sendEmail',
    success: function (result) {
      return;
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.sendEmailAdmin = function(orderID) {
  var params = {
    type: 'GET',
    url: '/api/order/' + orderID + '/sendEmailAdmin',
    success: function (result) {
      return;
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.sendEmailContactAdmin = function(ContactID) {
  var params = {
    type: 'GET',
    url: '/api/contact/' + ContactID + '/sendEmailAdmin',
    success: function (result) {
      return;
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.sendEmailSubscribeAdmin = function(subscribe_id) {
  var params = {
    type: 'GET',
    url: '/api/subscriber/' + subscribe_id + '/sendEmailAdmin',
    success: function (result) {
      return;
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.addReview = function(data, callback) {
  var params = {
    type: 'POST',
    url: '/api/review',
    data: data,
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.getReview = function(post_type, post_id, sortby, callback) {
  var params = {
    type: 'GET',
    url: '/api/review?post_type=' + post_type + '&post_id=' + post_id + '&sortby=' + sortby,
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.likeReview = function(id, callback) {
  var params = {
    type: 'POST',
    url: '/api/review/' + id + '/like',
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.dislikeReview = function(id, callback) {
  var params = {
    type: 'POST',
    url: '/api/review/' + id + '/dislike',
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.customerReviewStatus = function(post_type, post_id, callback) {
  var params = {
    type: 'GET',
    url: '/api/customer/review?post_type=' + post_type + '&post_id=' + post_id,
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.customerReview = function(callback) {
  var params = {
    type: 'GET',
    url: '/api/customer/listReview',
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.getShipping = function(regionID, subRegionID, total, callback) {
  var params = {
    type: 'GET',
    url: '/api/getShipping?region_id=' + regionID + '&subregion_id=' + subRegionID + '&total=' + total,
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.getProduct = function(product_id, callback) {
  var params = {
    type: 'GET',
    url: '/api/product/' + product_id,
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.addSubscriber = function (email, type, callback) {
  var params = {
    type: 'POST',
    url: '/api/subscriber',
    data: {
      email: email,
      type: type
    },
    success: function(line_item) {
      if ((typeof callback) === 'function') {
        callback(line_item);
      } else {
        StoreAPI.onSendSubscribe();
      }
      if (!line_item.code) {
        StoreAPI.sendEmailSubscribeAdmin(line_item.subscribe_id);
      }
    },
    error: function(XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);

}

StoreAPI.updateMetafield = function(data, callback) {
  data.handle = createHandle(data.title);
  console.log(data);
  var params = {
    type: 'POST',
    url: '/api/metafield',
    data: data,
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

StoreAPI.getSale = function(product_id, variant_id, callback) {
  var params = {
    type: 'GET',
    url: '/api/getSale?product_id=' + product_id + '&variant_id=' + variant_id,
    success: function (result) {
      callback(result);
    },
    error: function (XMLHttpRequest, textStatus) {
      StoreAPI.onError(XMLHttpRequest, textStatus);
    }
  };
  jQuery.ajax(params);
}

if (jQuery.fn.jquery >= '1.4') {
  StoreAPI.param = jQuery.param;
} else {
  StoreAPI.param = function(a) {
    var s = [],
      add = function(key, value) {
        value = jQuery.isFunction(value)
          ? value()
          : value;
        s[s.length] = encodeURIComponent(key) + "=" + encodeURIComponent(value);
      };
    if (jQuery.isArray(a) || a.jquery) {
      jQuery.each(a, function() {
        add(this.name, this.value);
      });
    } else {
      for (var prefix in a) {
        StoreAPI.buildParams(prefix, a[prefix], add);
      }
    }
    return s.join("&").replace(/%20/g, "+");
  }
  StoreAPI.buildParams = function(prefix, obj, add) {
    if (jQuery.isArray(obj) && obj.length) {
      jQuery.each(obj, function(i, v) {
        if (rbracket.test(prefix)) {
          add(prefix, v);
        } else {
          StoreAPI.buildParams(prefix + "[" + (typeof v === "object" || jQuery.isArray(v)
            ? i
            : "") + "]", v, add);
        }
      });
    } else if (obj != null && typeof obj === "object") {
      if (StoreAPI.isEmptyObject(obj)) {
        add(prefix, "");
      } else {
        jQuery.each(obj, function(k, v) {
          StoreAPI.buildParams(prefix + "[" + k + "]", v, add);
        });
      }
    } else {
      add(prefix, obj);
    }
  }
  StoreAPI.isEmptyObject = function(obj) {
    for (var name in obj) {
      return false;
    }
    return true;
  }
  StoreAPI.ExpressionSpecialChars = [
    {
      key: '(',
      val: '%26'
    }, {
      key: ')',
      val: '%27'
    }, {
      key: '|',
      val: '%28'
    }, {
      key: '-',
      val: '%29'
    }, {
      key: '&',
      val: '%30'
    }
  ];
  StoreAPI.encodeExpressionValue = function(val) {
    if ((typeof val) !== 'string' || val == null || val == "")
      return val;
    val = val.replace('%', '%25');
    for (n = 0; n < StoreAPI.ExpressionSpecialChars.length; n++) {
      var char = StoreAPI.ExpressionSpecialChars[n];
      val = val.replace(char.key, char.val);
    }
    return val;
  }
}

function floatToString(numeric, decimals) {
  var amount = numeric.toFixed(decimals).toString();
  amount.replace('.', StoreAPI.decimal);
  if (amount.match('^[\.' + StoreAPI.decimal + ']\d+')) {
    return "0" + amount;
  } else {
    return amount;
  }
}

function attributeToString(attribute) {
  if ((typeof attribute) !== 'string') {
    attribute += '';
    if (attribute === 'undefined') {
      attribute = '';
    }
  }
  return jQuery.trim(attribute);
}

function createHandle(str) {
  if (str) {
    str = str.trim();
    str = str.toLowerCase();
    str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
    str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
    str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
    str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
    str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
    str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
    str = str.replace(/đ/g, "d");
    str = str.replace(/\,/g, '-');
    str = str.replace(/\./g, '-');
    str = str.replace(/\!/g, '-');
    str = str.replace(/\?/g, '-');
    str = str.replace(/\~/g, '-');
    str = str.replace(/\ /g, '-');
    str = str.replace(/\|/g, '-');
    str = str.replace(/\./g, '-');
    str = str.replace(/\"/g, '-');
    str = str.replace(/\'/g, '-');
    str = str.replace(/\-\-+/g, '-');
    str = str.replace(/\s+/g, '-');
    str = str.replace(/[^\w\-]+/g, '');
    str = str.replace(/\-\-+/g, '-');
    str = str.replace(/^-+/, '');
    str = str.replace(/-+$/, '');
    if (str.slice(-1) == '-') str = str.substring(0, str.length - 1);
  }
  return str;
}
