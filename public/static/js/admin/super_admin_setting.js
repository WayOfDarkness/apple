$(document).ready(function () {

  var getSetting = function() {
    var promise = new Promise(function (resolve) {
      $.get('/admin/api/view/settingAdmin', function (res) {
        if (res && typeof (res) == 'string') {
          $('#content').html(res);
        } else {
          if (res.success) {
            settings = JSON.parse(res.settings);
            var content = renderComponent(settings);
            $('#content').html(content);
          } else {
            $('#content').html(res.message);
          }
        }
        resolve('success');
      });
    });
    return promise;
  };

  var getData = function(data){
    if (data == 'success'){
      var promise = new Promise(function (resolve) {
        setTimeout(function () {
          $.get('/admin/api/getSuperAdminSetting', function (json) {
            if (!json.code) {
              loadData(json.data);
              getVersionSetting();
            }
          })
        }, 1000);
      });
      return promise;
    }
  }
  getSetting().then(getData);
})

$('.btn-update-setting-website').click(function(e) {
  e.preventDefault();
  var data = {};
  var btn = $(this);
  $(document).find('.content :input:not(".upload")').each(function(index, element) {
    var self = $(this);
    var name = element.name;
    if (self.hasClass('tinymce')) {
      data[name] = tinyMCE.get(name).getContent();
    } else if (self.hasClass('tagsinput')) {
      data[name] = self.tagsinput('items');
    } else if (self.attr("type") == "radio") {
      if (self.is(':checked')) data[name] = self.val();
    } else if (self.attr("type") == "checkbox") {
      data[name] = 0;
      if (self.is(':checked')) data[name] = 1;
    } else if (name) {
      data[name] = self.val();
    }
  });
  if (data['slug_type'] == 2) {
    $('input[name^="slug_type_2"]').each(function () {
      var name = $(this).attr('name');
      if (!$(this).val()) {
        data[name] = $(this).attr('placeholder');
      }
    });
  } else if (data['slug_type'] == 3) {
    $('.box-setting-url[data-type="3"]').find('input[name^="slug_lv_1_"]').each(function () {
      var name = $(this).attr('name');
      if ($(this).val()) {
        data[name] = $(this).val();
      } else data[name] = $(this).attr('placeholder');
    });
  } else if (data['slug_type'] == 4) {
    $('.box-setting-url[data-type="4"]').find('input[name^="slug_lv_1_"]').each(function () {
      var name = $(this).attr('name');
      if ($(this).val()) {
        data[name] = $(this).val();
      } else data[name] = $(this).attr('placeholder');
    });
    $('.box-setting-url[data-type="4"]').find('input[name^="slug_lv_2_"]').each(function () {
      var name = $(this).attr('name');
      if (!$(this).val()) {
        data[name] = $(this).attr('placeholder');
      }
    });
  }
  btn.addClass('disabled');
  $.ajax({
    type: 'PUT',
    url: '/admin/superAdminSetting',
    data: JSON.stringify(data),
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
});

$(document).on('change', 'input[name="slug_type"]', function() {
  $(document).find('.box.box-setting-url').addClass('hidden');
  $(document).find('.box.box-setting-url[data-type="' + $(this).val() + '"]').removeClass('hidden');
});

var parent_id = 0;
$(document).on('click','.btn-add-attribute' ,function() {
  var type = $(this).data('type');
  if (type == 'attribute') parent_id = -1;
  else if (type == 'option') parent_id = -2;
  $('#modal-product-attribute').modal('show');
});

$(document).on('click', '#modal-product-attribute .btn-admin', function() {
  var modal = $(this).closest('.modal');
  modal.find('.error').removeClass('error');
  var id = $(this).attr('data-id');
  var data = {};
  data.parent_id = parent_id;
  data.name = modal.find('input[name="name"]').val();
  data.name_en = modal.find('input[name="name_en"]').val();
  if (!data.name) {
    modal.find('input[name="name"]').addClass('error');
    return toastr.error("Chưa nhập tên thuộc tính");
  }
  data.status = modal.find('select[name="status"]').val();

  var self = $(this);
  self.addClass('disabled');
  if (id) {
    $.ajax({
      type: 'PUT',
      url: '/admin/attribute/' + id,
      data: data,
      success: function(res) {
        self.removeClass('disabled');
        if (!res.code) {
          toastr.success('Cập nhật thành công');
          reloadPage();
        } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
      }
    });
  } else {
    $.post('/admin/attribute', data, function (res) {
      self.removeClass('disabled');
      if (!res.code) {
        toastr.success("Thành công");
        reloadPage();
      } else if (res.code == -1) {
        toastr.error("Thuộc tính đã tồn tại");
      }
    });
  }
});

$(document).on('click', '.btn-delete-attribute', function() {
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  var nameAtt = tr.find('td:eq(1)').text();
  popupConfirm("Xóa thuộc tính "+ nameAtt, function(ok) {
    if (ok) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/attribute/' + id,
        success: function (json) {
          if (!json.code) {
            toastr.success('Xóa thành công thuộc tính '+ nameAtt);
            tr.remove();
            reloadPage();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      })
    }
  });
});

$(document).on('click', '.btn-edit-attribute', function () {
  var id = $(this).data('id');
  $.get('/admin/attribute/' + id, function(res) {
    if (!res.code) {
      console.log(res.data);
      var modal = $('#modal-product-attribute');
      modal.find('input[name="name"]').val(res.data.name);
      modal.find('select[name="status"]').val(res.data.status);
      modal.find('.btn-admin').attr('data-id', res.data.id);
      modal.modal('show');
    } else toastr.error('Không tìm thấy thuộc tính');
  });
});

$('.btn-restore-setting').click(function() {
  $(this).addClass('hidden');
  $('.item-restore').removeClass('hidden');
});

function getVersionSetting() {
  $.get('/admin/api/superAdminSetting/version', function(res) {
    if (res.code) {
      var options = '';
      $.each(res.version, function(index, elem) {
        if (elem) {
          if (!index) {
            options += '<option value="' + elem + '">Phiên bản hiện tại</option>';
          } else {
            var file = elem.replace('superadmin_', '');
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
  $.get('/admin/api/loadSuperadminSetting?file=' + file, function(res) {
    if (!res.code) {
      checkRestore = 1;
      fileRestore = file;
      loadData(res.data)
    };
  });
});

function loadData(data) {
  $('.tagsinput').tagsinput();
  data.forEach(function (element, index) {
    if (element.key == 'slug_type') {
      $(document).find('.box.hidden[data-type="' + element.value + '"]').removeClass('hidden');
    }
    var tag = $('.content').find('[name=' + element.key + ']');
    if (tag.attr("type") == "radio") {
      $('input[name="' + element.key + '"][value="' + element.value + '"]').prop('checked', true);
    } else if (tag.attr("type") == "checkbox") {
      if (element.value == '1') {
        $(tag).prop('checked', true);
      }
    } else {
      tag.val(element.value);
    }
    if (tag.prop('tagName') == 'textarea') {
      tag.val(element)
    }
    if (tag.hasClass('tagsinput')) {
      var temp = element.value;
      temp.forEach(function (t, i) {
        tag.tagsinput('add', t)
      })
    }
    if (tag.hasClass('ajax-source') && tag.attr('multiple')) {
      var value = element.value;
      var mapNumber = value.map(Number);
      setTimeout(function () {
        mapNumber.forEach(function (val, index) {
          tag.find('option[value="' + val + '"]').attr('selected', 'selected');
        });
        tag.trigger("chosen:updated");
      }, 3000);
    }
    if (element.key == 'custom_field') {
      if (element.value) {
        var stt = 1;
        for (var post_type in element.value) {
          var data = element.value[post_type];
          for (var i = 0; i < data.length; i++) {
            var obj = {
              stt: stt,
              title: data[i].title,
              handle: data[i].handle,
              post_type: post_type,
              input_type: data[i].input_type
            };
            var tr = tmpl('tr_table_custom_field', obj);
            $('.table-custom-field tbody').append(tr);
            $('.table-custom-field').removeClass('hidden');
            stt++;
          }
        }
      }
    }
    initTinymce('.tinymce');
  });
  $('.content').find('form input[type="hidden"]').each(function () {
    var inputPrev = $(this).closest('form').find('img');
    var value = $(this).val();
    if (value) inputPrev.attr('src', '/uploads/' + value);
    else inputPrev.attr('src', '/static/img/default.jpg');
  });
}
