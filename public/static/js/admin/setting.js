var latlng = '';
var test;
var checkRestore = 0;
var fileRestore = '';

$(document).ready(function () {
  var getSetting = function() {
    var promise = new Promise(function (resolve) {
      $.get('/admin/api/view/setting', function (res) {
        if (res && typeof(res) == 'string') {
          $('#content').html(res);
          $('.tagsinput').tagsinput();
        } else {
          if (res.success) {
            settings = JSON.parse(res.settings);
            var content = renderComponent(settings);
            $('#content').html(content);
            $('.tagsinput').tagsinput();
            autocomplete();
            if ($(document).find('div[data-type="maps"]').length) {
               $.getScript("https://maps.googleapis.com/maps/api/js?key=AIzaSyAkK1Nj9HWtb4R0crJISga3j9hq2aBC8lQ&libraries=places&callback=makeMap");
            }
          } else {
            $('#content').html(res.message);
          }
        }
        resolve('success');
      });
    });
    return promise;
  };

  var getAjaxSource = function(data) {
    if (data == 'success'){
      var promise = new Promise(function (resolve) {
        if (!$('.content .ajax-source').length) resolve('success');
        else {
          var count = 0;
          $('.content .ajax-source').each(function () {
            var self = $(this);
            if (self.data('type')) {
              var type = self.data('type');
              $.get('/admin/api/setting/getList?type=' + type, function (json) {
                if (!json.code) {
                  if (json.data) {
                    self.html('');
                    json.data.forEach(function (element, index) {
                      var html = '';
                      if (type == 'tag') {
                        html = '<option value=' + element.name + '>' + element.name + '</option>';
                      } else {
                        html = '<option value=' + element.handle + '>' + element.title + '</option>';
                      }
                      self.append(html);
                    });

                    if (self.attr('multiple') && self.hasClass('ajax-source')) {
                      self.chosen({
                        width: "100%"
                      });
                    }
                  }
                }
                count++;
                if (count == $('.content .ajax-source').length) {
                  resolve('success');
                }
              });
            }
          });
        }
        initTinymce('.tinymce');
      });
      return promise;
    }
  };

  var getData = function(data) {
    if (data == 'success') {
      var promise = new Promise(function (resolve) {
        setTimeout(function () {
          $.get('/admin/api/getSetting', function (res) {
            if (!res.code) {
              loadData(res.data);
              getVersionSetting();
              $('.btn-update-setting-website').removeClass('disabled');
              resolve(1);
            }
          })
        }, 1000);
      })
      return promise;
    }
  };
  getSetting().then(getAjaxSource).then(getData);

  function loadData(data) {
    $('.content :input:not(".upload"), .tinymce').val('');
    data.forEach(function (element, index) {
      if (element.key == 'latlng' && element.value) {
        var tempLatLng = element.value;
        var latLng = {
          lat: tempLatLng.split(',')[0] - 0,
          lng: tempLatLng.split(',')[1] - 0
        }
        if ($('#myMap').length) makeMap(latLng);
      }
      var tag = $('.content').find('[name=' + element.key + ']');

      if (tag.hasClass('tinymce') && tag.attr('id')) {
        var id = tag.attr('id');
        if (id) tinymce.get(id).setContent(element.value);
      } else if (tag.hasClass('tagsinput')) {
        var temp = element.value;
        if (temp) {
          tag.tagsinput('removeAll');
          temp.forEach(function (t, i) {
            tag.tagsinput('add', t);
          });
        }
      } else if (tag.attr('multiple') == 'multiple') {
        var value = element.value || [];
        if (value && value.length) {
          ChosenOrder.setSelectionOrder(tag.get(0), value, true);
        }
      } else if (tag.attr("type") == "radio") {
        $('input[name="' + element.key + '"][value="' + element.value + '"]').prop('checked', true);
      } else if (tag.attr("type") == "checkbox") {
        if (element.value == '1') {
          $(tag).prop('checked', true);
        }
      } else {
        tag.val(element.value);
      }

      var object = $('.content').find('[data-name=' + element.key + ']');
      if (object && object.length) {
        var array_object = ['product', 'article', 'blog', 'collection', 'page', 'gallery'];
        var multiple = object.attr('data-multiple') || false;
        object.find('.div-item').remove();
        if (multiple) {
          $.each(element.value, function (i, e) {
            var item = tmpl('search-object-item', {
              handle: e.handle,
              title: e.title
            });
            object.find('.search-object').before(item);
          });
        } else {
          $.get('/admin/api/setting/' + element.key + '_title', function (res) {
            if (res) {
              var item = tmpl('search-object-item', {
                handle: typeof(element.value) == 'object' ? element.value.handle : element.value,
                title: res
              });
              object.find('.search-object').before(item);
              object.find('.search-object').prop('disabled', true);
            }
          });
        }
      }

    });

    setTimeout(function () {
      var hostname = location.protocol + '//' + location.host;
      $('.content').find('.value.file').each(function () {
        var file_name = $(this).val();
        if (file_name) {
          var link = hostname + '/files/' + file_name;
          $(this).parent().append('<p class="link" style="margin-top: 10px;">Link file: <a target="_blank" href="' + link + '">' + link + '</a><i class="remove-file ico-times"></i></p>');
        }
      });

      $('.content').find('.item-choose-image input[type="hidden"]').each(function () {
        var inputPrev = $(this).closest('.item-choose-image').find('img');
        var value = $(this).val();
        if (value) {
          $(this).closest('.item-choose-image').addClass('active');
          inputPrev.attr('src', '/uploads/' + value);
        } else {
          inputPrev.attr('src', '/static/img/default.jpg');
        }
      });
    }, 1000);
  }

  function getVersionSetting() {
    $.get('/admin/api/setting/version', function(res) {
      if (res.code) {
        var options = '';
        $.each(res.version, function(index, elem) {
          if (elem) {
            if (!index) {
              options += '<option value="' + elem + '">Phiên bản hiện tại</option>';
            } else {
              var file = elem.replace('admin_', '');
              file = file.replace('.php', '');
              var temp = file.split('_');
              file = temp[0] + '/' + temp[1] + '/' + temp[2] + ' ' + temp[3] + ':' + temp[4] + ':' + temp[5];
              options += '<option value="' + elem + '">' + file + '</option>';
            }
          }
        });
        $('select[name="change_version_setting"]').append(options);
      }
    });
  }

  $('select[name="change_version_setting"]').change(function() {
    var file = $(this).val();
    $.get('/admin/api/loadSetting?file=' + file, function(res) {
      if (!res.code) {
        checkRestore = 1;
        fileRestore = file;
        loadData(res.data)
      };
    });
  });

  function autocomplete() {
    $(document).find('.box-object-setting .search-object').each(function(index, element) {
      var parent = $(this).closest('.box-object-setting');
      var multiple = parent.attr('data-multiple') || false;
      var type = parent.attr('data-type');
      $(this).on("keydown", function (event) {
          if (event.keyCode === $.ui.keyCode.TAB &&
            $(this).autocomplete("instance").menu.active) {
            event.preventDefault();
          }
        })
        .autocomplete({
          source: function (request, response) {
            $.getJSON("/admin/api/object/" + type, {
              term: request.term
            }, function (data) {
              if (data && data.length) response(data);
              else {
                var result = [{
                  label: 'No matches found',
                  value: 'No matches found'
                }];
                response(result);
              }
            });
          },
          search: function () {
            if (this.value.length < 1) {
              return false;
            }
          },
          select: function (event, ui) {
            if (ui.item.value != 'No matches found') {
              this.value = '';
              var item = tmpl('search-object-item', {
                handle: ui.item.handle,
                title: ui.item.value
              });
              parent.find('.search-object').before(item);
              if (!multiple) {
                parent.find('.search-object').prop('disabled', true);
              }
            }
            return false;
          }
        });
    });
  }

  $(document).on('click', '.box-object-setting .fa-times', function() {
    $(this).closest('.box-object-setting').find('.search-object').prop('disabled', false);
    $(this).closest('.div-item').remove();
  });

  $('.type-item').on('change', function () {
    if ($(this).val() == 'block') {
      $('.num-block').addClass('disabled');
    }
    else {
      $('.num-block').removeClass('disabled');
      $('.num-block').html('');
      $('.num-block').append('<option value="0">Chọn block cần thêm vào</option>')
      $('.content .box').each(function (index, element) {
        var html = '<option value=' + parseInt(index + 1) + '>Block ' + parseInt(index + 1) + '</option>'
        $('.num-block').append(html)
      })
    }
  })

  $('.select-type select').on('change', function () {
    var self = $(this);
    var value = $(this).val();
    $.get('/admin/api/setting/getList?type=' + value, function (json) {
      if (!json.code) {
        self.closest('.form-group').find('.select-item select').html('')
        json.data.forEach(function (element, index) {
          var html = '<option value=' + parseInt(element.id) + '>' + element.title + '</option>'
          self.closest('.form-group').find('.select-item select').append(html)
        })
      }
    })
  })
})

$('.btn-update-setting-website').click(function () {
  var data = {};
  var btn = $(this);
  if (!validation()) {
    $('.content :input:not(".upload"), .tinymce').each(function (index, element) {
      var self = $(this);
      var name = element.name;
      if (name) {
        if (self.hasClass('tinymce')) {
          data[name] = tinyMCE.get(name).getContent();
        } else if (self.hasClass('tagsinput')) {
          data[name] = self.tagsinput('items');
        } else if (self.attr("type") == "radio") {
          if (self.is(':checked')) data[name] = self.val();
        } else if (self.attr("type") == "checkbox") {
          data[name] = 0;
          if (self.is(':checked')) data[name] = 1;
        } else if (self.attr("multiple") && self.hasClass('ajax-source')) {
          data[name] = ChosenOrder.getSelectionOrder(self.get(0));
        } else {
          data[name] = self.val();
        }
      }
    });

    $('.box-object-setting').each(function() {
      var name = $(this).data('name');
      var type = $(this).data('type');
      var array_object = ['product', 'article', 'blog', 'collection', 'page', 'gallery'];
      var multiple = $(this).data('multiple') || false;
      if (multiple) {
        var values = [];
        if ($(this).find('.div-item').length) {
          $(this).find('.div-item label').each(function () {
            if ($(this).attr('data-handle')) values.push({
              title: $(this).text().trim(),
              handle: $(this).attr('data-handle')
            });
          });
        }
        data[name] = values;
      } else {
        if (array_object.indexOf(type) > -1) {
          var value = {
            title: $(this).find('.div-item label').text().trim(),
            handle: $(this).find('.div-item label').attr('data-handle')
          };
          data[name] = value;
          data[name + '_title'] = $(this).find('.div-item label').text().trim();
        }

        if($(this).find('input').prop('id') == 'google-address') {
          data[name] = $(this).find('input').val();
        }
      }
    });

    if (latlng) {
      data['latlng'] = latlng;
    }

    $.each(data, function (index, element) {
      if (Array.isArray(element) && !element.length){
        data[index] = '';
      }
    });

    btn.addClass('disabled');
    $.ajax({
      type: 'PUT',
      url: '/admin/setting',
      data: JSON.stringify({
        data: data,
        checkRestore: checkRestore,
        fileRestore: fileRestore
      }),
      contentType: "application/json",
      dataType: 'json',
      success: function (json) {
        btn.removeClass('disabled');
        if (!json.code) {
          toastr.success('Cập nhật thành công');
          reloadPage();
        } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
      }
    });
  }
});

$(document).on('change', '.upload', function () {
  var self = $(this);
  if (checkExtImage($(this).val())) {
    var form = $(this).closest('form');
    var formData = new FormData(form[0]);
    $.ajax({
      type: 'POST',
      url: '/admin/api/uploadImage/tab-home',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success: function (json) {
        if (!json.code) {
          var image = json.data;
          var timestamp = new Date() - 0;
          form.find('[type="hidden"]').val(image);
          form.find('img').attr('src', '/uploads/' + image + '?v=' + timestamp);
        } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
      }
    });
  }
});

function makeMap(location){
  var zoom = 18;
  if (!location) {
    var location = {
      lat: 10.8230989,
      lng: 106.6296638
    }
    zoom = 10;
  }

  var map = new google.maps.Map(document.getElementById('myMap'), {
    zoom: zoom,
    center: location
  });
  var marker = new google.maps.Marker({
    position: location,
    draggable: true,
    animation: google.maps.Animation.DROP,
    map: map
  });
  google.maps.event.addListener(marker, 'dragend', function () {
    moveMarker(marker.getPosition());
    latlng = marker.position.lat() + ',' + marker.position.lng();
  });

  var autocomplete = new google.maps.places.Autocomplete($("#google-address")[0], {});

  google.maps.event.addListener(autocomplete, 'place_changed', function () {
    var place = autocomplete.getPlace();
    initMaps(place.formatted_address);
    latlng = autocomplete['lat'] + ',' + autocomplete['lng'];
  });
}

function initMaps(address) {
  if (!address) address = 'Quận 1, Hồ Chí Minh';
  $.ajax({
    type: 'GET',
    url: 'https://maps.googleapis.com/maps/api/geocode/json?address=' + address + '&key=AIzaSyAoK_hfMfMj30B0PPgJ0qg9sA6kjPVO6QA',
    success: function (json) {
      if (json.status === 'OK') {
        var location = json.results[0].geometry.location;
        latlng = location['lat'] + ',' + location['lng'];
        var map = new google.maps.Map(document.getElementById('myMap'), {
          zoom: 18,
          center: location
        });
        var marker = new google.maps.Marker({
          position: location,
          draggable: true,
          animation: google.maps.Animation.DROP,
          map: map
        });
        google.maps.event.addListener(marker, 'dragend', function () {
          moveMarker(marker.getPosition());
          latlng = marker.position.lat() + ',' + marker.position.lng();
        });

        var autocomplete = new google.maps.places.Autocomplete($("#google-address")[0], {});

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
          var place = autocomplete.getPlace();
          makeMap(place.formatted_address);
          latlng = autocomplete['lat'] + ',' + autocomplete['lng'];
        });
      } else {
        toastr.error('Không thể định vị, vui lòng thử lại!');
      }
    }
  })
}

function moveMarker(pos) {
  geocoder = new google.maps.Geocoder();
  geocoder.geocode({
      latLng: pos
    },
    function (results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        $("#google-address").val(results[0].formatted_address);
      } else {
        toastr.error('Không thể định vị!');
      }
    }
  );
}

function validation() {
  var check = 0;
  $('.content :input:not(".upload, #google-address")').removeClass('error');
  $('.content :input:not(".upload, #google-address")').each(function (index, element) {
    var self = $(this)
    var name = element.name;
    if (self.val() && self.hasClass('email')){
      if (!isEmail(self.val())){
        self.addClass('error');
        check = 1;
        toastr.error(name + ' chưa đúng định đạng!');
      }
    }
    if (self.val() && self.hasClass('phone')){
      if (!isPhone(self.val())){
        self.addClass('error');
        check = 1;
        toastr.error(name + ' chưa đúng định đạng!');
      }
    }
  })
  return check;
}


$('.btn-restore-setting').click(function() {
  $(this).addClass('hidden');
  $('.item-restore').removeClass('hidden');
});
