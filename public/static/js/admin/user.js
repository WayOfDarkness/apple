initDataTable('table');

$('.btn-create-update').click(function() {
  var id = $(this).data('id');
  $('input, select').removeClass('error');
  var data = {};
  data.role_id = $('select[name="role_id"]').val();
  data.name = $('input[name="name"]').val();
  data.phone = $('input[name="phone"]').val();
  data.email = $('input[name="email"]').val();

  if(!data.name) {
    $('input[name="name"]').addClass('error');
    return toastr.error('Chưa nhập họ tên');
  }
  var ck_password =  /^[A-Za-z0-9-]/;
  if (!ck_password.test(data.name)) {
    $('input[name="name"]').addClass('error');
    return toastr.error('Họ tên không đúng định dạng');
  }

  if(!data.email) {
    $('input[name="email"]').addClass('error');
    return toastr.error('Chưa nhập email');
  }

  if (!isEmail(data.email)) {
    $('input[name="email"]').addClass('error');
    return toastr.error('Chưa đúng định dạng email');
  }

  if (data.phone && !isPhone(data.phone)) {
    $('input[name="phone"]').addClass('error');
    return toastr.error('Số điện thoại chưa đúng định dạng');
  }

  if (!data.role_id) {
    $('select[name="role_id"]').addClass('error');
    return toastr.error('Chưa chọn phân quyền');
  }

  $(this).addClass('disabled');
  $('input[name="email"]').addClass('disabled');

  if (id) updateUser(id, data);
  else createUser(data);

});

function createUser(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/user',
    data: data,
    dataType: 'json',
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        if (json.codeSend == -1){
          toastr.warning(json.message);
          reloadPage('/admin/user');
        } else{
          toastr.success(json.message);
          reloadPage('/admin/user');
        }
      } else if (json.code == -1) {
        toastr.error('Quản trị viên đã tồn tại');
      } else if (json.code == -4) {
        toastr.error(json.message);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    },
    error: function (resp) {
      var str = resp.responseText;
      var string = str.substring(str.lastIndexOf("{"),str.lastIndexOf("}")+1);
      var json = JSON.parse(string);
      if(!json.code) {
        toastr.success(json.message);
        reloadPage('/admin/user/' + json.id);
      } else if (json.code == -1) {
        toastr.error('Quản trị viên đã tồn tại');
      } else if (json.code == -4) {
        toastr.error(json.message);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateUser(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/user/' + id,
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        toastr.success('Cập nhật thành công');
        reloadPage();
      } else if (json.code == -1) {
        toastr.error('Quản trị viên đã tồn tại');
      } else if (json.code == -4) {
        toastr.error(json.message);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}


$(document).on('click', '.btn-remove', function() {
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  popupConfirm("Xóa quản trị viên?", function(result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/user/' + id,
        success: function(json) {
          if(!json.code) {
            toastr.success('Xóa thành công');
            tbl.row(tr).remove().draw();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      });
    }
  });
});

$(document).on('click', '.btn-send-email', function () {
  var random = $(this).data('random');
  $.ajax({
    type: 'GET',
    url: '/admin/api/resendEmail/user/' + random,
    success: function (json) {
      if (!json.code) {
        toastr.success('Gửi thành công');
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    },
    error: function (er) {
      var responseText = er.responseText
      if (responseText.indexOf('"code":0') != -1){
        toastr.success('Gửi email thành công');
      }
    }
  })
})
