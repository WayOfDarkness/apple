var chartVisit;
var chartRevenue;

$(document).ready(function () {

  //hover show action contact
  // $('.mt-comment').hover(function () {
  //   $(this).css('background-color', 'white');
  //   $(this).find('.mt-comment-details').removeClass('hidden');
  // }, function () {
  //   $(this).css('background-color', '');
  //   $(this).find('.mt-comment-details').addClass('hidden');
  // })

  $('input[name="visit"]').click(function () {
    var numOfDays = $(this).val();
    $(this).closest('.optionchart').find('label').removeClass('btn-danger');
    $(this).closest('label').addClass('btn-danger');
  })

  //aminate num
  $('.count').each(function () {
    $(this).text(numeral($(this).data('value')).format('0.0a'));
    $(this).prop('Counter', 0).animate({
      Counter: numeral($(this).data('value')).format('0.0a')
    }, {
      duration: 3000,
      easing: 'swing',
      step: function (now) {
        var last = numeral($(this).data('value')).format('0.0a').slice(-1);
        if ($.isNumeric(last)){
          $(this).text(Math.ceil(now));
        }
        else{
          $(this).text(Math.round(now*10)/10 + last.toUpperCase());
        }
      }
    });
  })

  //fullscreen
  $('.btn-fullscreen-visit').click(function () {
    $(this).parent().toggleClass('padding-right-added');
    $('#visitchart').toggleClass('full-height');
    $(this).closest('.box-visit').toggleClass('fullscreen');
    // loadDataVisit();
    $(this).find('i').toggleClass('fa-expand').toggleClass('fa-compress');
  })
  //fullscreen
  $('.btn-fullscreen-revenue').click(function () {
    $(this).parent().toggleClass('padding-right-added');
    $('#revenuechart').toggleClass('full-height');
    $(this).closest('.box-revenue').toggleClass('fullscreen');
    $(this).closest('.chart-block').toggleClass('fullscreen-chart');
    // loadDataVisit();
    $(this).find('i').toggleClass('fa-expand').toggleClass('fa-compress');
  })

  //time since history
  $('.mt-log-right p').each(function (index, element) {
    var timeResult = timeSince($(this).text());
    $(this).html(timeResult);
  });

  //graph Revenue
  // loadDataRevenue();

});

// $(document).on('change', '.revenue-select', function () {
//   // loadDataRevenue();
// })

//contact
$(document).on('click', '.btn-remove-contact', function () {
  var id = $(this).data('value');
  var block = $(this).closest('.mt-comment');
  popupConfirm('Bạn có muốn xóa liên hệ ?', function (result) {
    if (result) {
      $.ajax({
        type: 'POST',
        url: '/admin/api/updateStatus',
        data: {
          type: 'contact',
          arrId: [id],
          status: 'delete'
        },
        success: function (json) {
          block.remove();
        }
      })
    }
  })
});

$(document).on('click', '.li-checklog', function () {
  $('.mt-log').each(function () {
    $(this).addClass('hidden');
  });
  $('input[name="checklog"]:checked').each(function () {
    var val = $(this).val();
    $('.mt-log').each(function () {
      if ($(this).data('type') == val){
        $(this).removeClass('hidden');
      }
    })
  });
});


function makeRevenueGraph(revenue, numOfDays) {
  var hash = {};
  revenue.forEach(function (elem, i) {
    hash[elem.date] = elem.sum;
  });
  var labels = [];
  var data = [];
  if (numOfDays == 7) {
    for (var i = numOfDays - 1; i >= 0; i--) {
      var date = new Date(new Date() - i * 86400000);
      var dateName = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '-' + ((date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : (date.getMonth() + 1));
      labels.push(dateName);
      if (hash[dateName]) {
        data.push(hash[dateName]);
      } else {
        data.push(0);
      }
    }
  }
  if (numOfDays == 14) {
    for (var i = numOfDays - 1; i >= 0; i -= 2) {
      var date = new Date(new Date() - i * 86400000);
      var dateName = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '-' + ((date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : (date.getMonth() + 1));
      labels.push(dateName);
      if (hash[dateName]) {
        data.push(hash[dateName]);
      } else {
        data.push(0);
      }
    }
  }
  if (numOfDays == 30) {
    for (var i = numOfDays - 1; i >= 0; i -= 4) {
      var date = new Date(new Date() - i * 86400000);
      var dateName = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '-' + ((date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : (date.getMonth() + 1));
      labels.push(dateName);
      if (hash[dateName]) {
        data.push(hash[dateName]);
      } else {
        data.push(0);
      }
    }
  }
  if (numOfDays == 60) {
    for (var i = numOfDays - 1; i >= 0; i -= 8) {
      var date = new Date(new Date() - i * 86400000);
      var dateName = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '-' + ((date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : (date.getMonth() + 1));
      labels.push(dateName);
      if (hash[dateName]) {
        data.push(hash[dateName]);
      } else {
        data.push(0);
      }
    }
  }
  return {
    labels: labels,
    data: data
  }
}

function timeSince(date) {
  var seconds = Math.floor((new Date() - new Date(date)) / 1000);
  var interval = Math.floor(seconds / 31536000);
  if (interval >= 1) {
    return interval + " năm trước";
  }
  interval = Math.floor(seconds / 2592000);
  if (interval >= 1) {
    return interval + " tháng trước";
  }
  interval = Math.floor(seconds / 86400);
  if (interval >= 1) {
    return interval + " ngày trước";
  }
  interval = Math.floor(seconds / 3600);
  if (interval >= 1) {
    return interval + " giờ trước";
  }
  interval = Math.floor(seconds / 60);
  if (interval >= 1) {
    return interval + " phút trước";
  }
  return Math.floor(seconds) + " giây trước";
}

$(function() {
  var start = moment().subtract(6, 'days');
  var end = moment();
  var label = 'Tuần';
  loadDataVisit(start.format('L'), end.format('L'))
  function cb(label) {
    $('#reportrange span').html(label);
  }
  $('#reportrange').daterangepicker({
    "autoApply": true,
    "alwaysShowCalendars": true,
    "startDate": start,
    "endDate": end,
    "ranges": {
      'Tuần': [moment().subtract(6, 'days'), moment()],
      'Tháng': [moment().subtract(29, 'days'), moment()]
    },
    // "minDate": moment().subtract(29, 'days'),
    "maxDate": end,
    "locale": {
      "customRangeLabel": "Tùy chọn",
    },
  }, function(start, end, label) {
    cb(label);
    label = label;
    loadDataVisit(start.format('L'), end.format('L'))
  });

  cb(label);

});

$(function() {
  var start = moment().subtract(6, 'days');
  var end = moment();
  var label = 'Tuần';
  loadDataRevenue(start.format('L'), end.format('L'));
  function cb2(label) {
    $('#revenue-action span').html(label);
  }
  $('#revenue-action').daterangepicker({
    "autoApply": true,
    "alwaysShowCalendars": true,
    "startDate": start,
    "endDate": end,
    "ranges": {
      'Tuần': [moment().subtract(6, 'days'), moment()],
      'Tháng': [moment().subtract(29, 'days'), moment()]
    },
    // "minDate": moment().subtract(29, 'days'),
    "maxDate": end,
    "locale": {
      "customRangeLabel": "Tùy chọn",
    },
  }, function(start, end, label) {
    cb2(label);
    label = label;
    loadDataRevenue(start.format('L'), end.format('L'))
  });

  cb2(label);

});

function loadDataRevenue(start, end) {
  $.ajax({
    type: 'post',
    url: '/admin/api/dashboard/getrevenue',
    data:{
      'start': start,
      'end': end
    },
    success: function (json) {
      if (!json.code) {
        var value = Intl.NumberFormat();
        $('.revenue-total').html(value.format(json.id.countTotal.total) + ' vnđ');
        $('.revenue-count').html(json.id.countTotal.count + ' đơn hàng');
        var days = json.days;
        var hash = {};
        json.id.sumOrder.forEach(function (elem, i) {
          hash[elem.date] = elem.sum;
        });
        var labels = [];
        var data = [];
        for (var i = days; i >= 0; i--) {
          var date = new Date(new Date(end) - i * 86400000);
          var dateName = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '-' + ((date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : (date.getMonth() + 1));
          labels.push(dateName);
          if (hash[dateName]) {
            data.push(hash[dateName]);
          } else {
            data.push(0);
          }
        // var graphData = makeRevenueGraph(json.id.sumOrder, numOfDays);
        $('#revenuechart').remove();
        $('iframe.chartjs-hidden-iframe').remove();
        $('.revenue-info').before('<canvas id="revenuechart" style="max-height: 65%;"><canvas>');
        if ($('.box-revenue').hasClass('fullscreen')) {
          $('#revenuechart').addClass('full-height');
        }
        var ctxRevenue = document.getElementById('revenuechart').getContext('2d');
        chartRevenue = new Chart(ctxRevenue, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [{
              backgroundColor: 'rgb(220, 74, 61)',
              borderColor: 'rgb(255, 99, 132)',
              data: data,
            }]
          },
          options: {
            legend: {
              display: false
            },
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: true
                }
              }]
            }
          }
        });
      }
    }
  }
  })
}
function loadDataVisit(start, end) {
  $.ajax({
    type: 'POST',
    url: '/admin/api/dashboard/getvisit',
    data:{
      'start': start,
      'end': end
    },
    success: function (json) {
      if (!json.code) {
        var days = json.days;
        var hash = {};
        json.id.forEach(function (elem, i) {
          hash[elem.date] = elem.sum;
        });
        var labels = [];
        var data = [];
        for (var i = days; i >= 0; i--) {
          var date = new Date(new Date(end) - i * 86400000);
          var dateName = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + '-' + ((date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : (date.getMonth() + 1));
          labels.push(dateName);
          if (hash[dateName]) {
            data.push(hash[dateName]);
          } else {
            data.push(0);
          }
        }
        $('#visitchart').remove();
        $('iframe.chartjs-hidden-iframe').remove();
        $('.child-box-visit').append('<canvas id="visitchart" style="max-height: 85%;"><canvas>');
        var ctxVisit = document.getElementById('visitchart').getContext('2d');
        ctxVisit.height = 500;
        chartVisit = new Chart(ctxVisit, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [{
              backgroundColor: 'rgb(120, 172, 214)',
              borderColor: 'rgb(53, 105, 214)',
              data: data,
            }]
          },
          options: {
            maintainAspectRatio: false,
            legend: {
              display: false
            },
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: true
                }
              }]
            }
          }
        });
      }
    }
  })
}
