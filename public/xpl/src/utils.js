var textMimes = {
  'application/x-empty': true,
  'application/javascript': true,
  'application/json': true,
  'application/xhtml+xml': true,
  'audio/x-mp3-playlist': true,
  'application/x-web-config': true,
  'application/docbook+xml': true,
  'application/x-php': true,
  'application/x-perl': true,
  'application/x-awk': true,
  'application/x-config': true,
  'application/x-csh': true,
  'application/xml': true,
  'application/sql': true
};

function mime2class(mimeType) {
  var prefix = 'xpl-icon-',
    mime = mimeType.toLowerCase(),
    isText = textMimes[mime];

  mime = mime.split('/');
  if (isText) {
    mime[0] += ' ' + prefix + 'text';
  }

  return prefix + mime[0] + (mime[1] ? ' ' + prefix + mime[1].replace(/(\.|\+)/g, '-') : '');
}

/**
 * Return formated file size
 *
 * @param  Number  file size
 * @return String
 */
function formatSize(s) {
  var n = 1,
    u = 'b';
  if (typeof s == 'object' && s.hasOwnProperty('size')) {
    s = s.size;
  }

  if (s == 'unknown') {
    return i18n('unknown');
  }

  if (s > 1073741824) {
    n = 1073741824;
    u = 'GB';
  } else if (s > 1048576) {
    n = 1048576;
    u = 'MB';
  } else if (s > 1024) {
    n = 1024;
    u = 'KB';
  }
  s = s / n;
  return (s > 0 ? (n >= 1048576 ? s.toFixed(2) : Math.round(s)) : 0) + ' ' + u;
}

var i18 = {
  en: {
    translator: '',
    language: 'English',
    direction: 'ltr',
    dateFormat: 'd.m.Y H:i',
    fancyDateFormat: '$1 H:i',
    nonameDateFormat: 'ymd-His',
    messages: {}
  },
  months: [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
  ],
  monthsShort: [
    'msJan',
    'msFeb',
    'msMar',
    'msApr',
    'msMay',
    'msJun',
    'msJul',
    'msAug',
    'msSep',
    'msOct',
    'msNov',
    'msDec'
  ],
  days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
  daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
};
var fancyFormat = i18.en.fancyDateFormat;

var NOW = new Date();
var today = new Date(NOW.getFullYear(), NOW.getMonth(), NOW.getDate()).getTime() / 1000;
var yesterday = today - 86400;
var dateFormat = 'd.m.Y H:i';
var fancyDateFormat = '$1 H:i';

function dateStringFormat(format, d) {
  var YEAR2K = new Date('2000-1-1').getTime();
  if (typeof d == 'number' && d < YEAR2K) {
    d = d * 1000;
  }

  var self = new Date(d),
    output,
    d,
    dw,
    m,
    y,
    h,
    g,
    i,
    s;

  if (!d) {
    self = new Date();
  }

  h = self.getHours();
  g = h > 12 ? h - 12 : h;
  i = self.getMinutes();
  s = self.getSeconds();
  d = self.getDate();
  dw = self.getDay();
  m = self.getMonth() + 1;
  y = self.getFullYear();

  output = format.replace(/[a-z]/gi, function(val) {
    switch (val) {
      case 'd':
        return d > 9 ? d : '0' + d;
      case 'j':
        return d;
      case 'D':
        return i18n(self.i18.daysShort[dw]);
      case 'l':
        return i18n(self.i18.days[dw]);
      case 'm':
        return m > 9 ? m : '0' + m;
      case 'n':
        return m;
      case 'M':
        return i18n(self.i18.monthsShort[m - 1]);
      case 'F':
        return i18n(self.i18.months[m - 1]);
      case 'Y':
        return y;
      case 'y':
        return ('' + y).substr(2);
      case 'H':
        return h > 9 ? h : '0' + h;
      case 'G':
        return h;
      case 'g':
        return g;
      case 'h':
        return g > 9 ? g : '0' + g;
      case 'a':
        return h >= 12 ? 'pm' : 'am';
      case 'A':
        return h >= 12 ? 'PM' : 'AM';
      case 'i':
        return i > 9 ? i : '0' + i;
      case 's':
        return s > 9 ? s : '0' + s;
    }
    return val;
  });

  return output;
}

/**
 * Return localized date
 *
 * @param  Object  file object
 * @return String
 */
function formatDate(file, t) {
  var ts = t || file.ts,
    date,
    format,
    output,
    d,
    dw,
    m,
    y,
    h,
    g,
    i,
    s;

  if (ts > 0) {
    const _date = new Date(ts * 1000);
    format = ts >= yesterday ? fancyFormat : dateFormat;

    output = dateStringFormat(format, _date);

    return ts >= yesterday ?
      output.replace('$1', i18n(ts >= today ? 'Today' : 'Yesterday')) :
      output;
  } else if (file.date) {
    return file.date.replace(/([a-z]+)\s/i, function(a1, a2) {
      return i18n(a2) + ' ';
    });
  }

  return i18n('dateUnknown');
}

//TODO: implement i18n
function i18n(str) {
  return str;
}

function getImageStyle(path) {
  return `background-image: url('${window.BASE_URL}?method=image&src=${path}')`;
}

function formatData(folder, file) {
  return {
    ...file,
    path: `${folder}/${file.name}`,
    time: formatDate(file, file.time * 1000),
    size: formatSize(file),
    className: mime2class(file.mime),
    thumb: file.mime.indexOf('image') > -1 ? 'thumb' : '',
    selected: false
  };
}

function getParam(key) {
  var url = window.location.search.substring(1);
  url = decodeURI(url);
  var mode = url.split('&');
  var ret = {};
  for (var i = 0; i < mode.length; i++) {
    var m = mode[i].split('=');
    if (m[0] == key) {
      return m[1];
    }
  }
  return "";
}

function sendMessage(event, data) {
  var msg = JSON.stringify({
    iframeId: getParam("iframeId"),
    event: event,
    data: data
  });
  window.parent.postMessage(msg, '*');
}

function bindEvent(element, eventName, eventHandler) {
  if (element.addEventListener){
      element.addEventListener(eventName, eventHandler, false);
  } else if (element.attachEvent) {
      element.attachEvent('on' + eventName, eventHandler);
  }
}

export default {
  getImageStyle,
  formatDate,
  formatData,
  formatSize,
  mime2class,
  sendMessage,
  bindEvent,
  getParam
};
