initDataTable('table');
var modelName = 'bank';

// $('.tinymce').each(function () {
//   var id = $(this).attr('id');
//   initTinymce('#' + id);
// });

$('.btn-create-update').click(function() {
  $(document).find('.error').removeClass('error');
  var data = {};
  data.user_name = $('input[name="user_name"]').val();
  data.bank_name = $('input[name="bank_name"]').val();
  data.bank_number = $('input[name="bank_number"]').val();
  data.branch = $('input[name="branch"]').val();
  data.note = $('textarea[name="note"]').val();
  data.status = $('select[name="status"]').val();

  if(!data.user_name.trim().length) {
    toastr.error('Chưa nhập Tên tài khoản');
    return $('input[name="user_name"]').addClass('error');
  }

  if(!data.bank_name.trim().length) {
    toastr.error('Chưa nhập Tên ngân hàng');
    return $('input[name="bank_name"]').addClass('error');
  }

  if(!data.bank_number.trim().length) {
    toastr.error('Chưa nhập số tài khoản');
    return $('input[name="bank_number"]').addClass('error');
  }


  // data.multiLang = [];
  //
  // if (languages) {
  //   $.each(languages, function (index, elem) {
  //     var obj = {};
  //     obj.lang = elem;
  //     obj.id = $('input[name="translattion_' + elem + '"]').val()
  //     obj.title = $('input[name="title_' + elem + '"]').val();
  //     obj.handle = $('input[name="handle_' + elem + '"]').val() || data.handle;
  //     obj.description = $('textarea[name="description_' + elem + '"]').val();
  //     obj.content = tinyMCE.get("content_" + elem).getContent();
  //     data.multiLang.push(obj);
  //   });
  // }

  $(this).addClass('disabled');

  var id = $(this).data('id');
  if (id) updateBank(id, data);
  else createBank(data);
});

function createBank(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/bank',
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        toastr.success('Tạo thành công');
        // updateSEO('page', json.id);
        // updateMetafield('page', json.id);
        // setTimeout(function(){},5000);
        reloadPage('/admin/bank');
      } else if (json.code == -1) {
        toastr.error('Ngân hàng đã tồn tại');
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateBank(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/bank/' + id,
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        toastr.success('Cập nhật thành công');
        // updateSEO('page', id);
        // updateMetafield('page', id);
        reloadPage('/admin/bank');
      } else if (json.code == -1) {
        toastr.error('Ngân hàng đã tồn tại');
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

$(document).on('click', '.btn-remove', function() {
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  popupConfirm('Xóa trang', function (result) {
    if (result) {
      $.ajax({
  			type: 'DELETE',
  			url: '/admin/bank/' + id,
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
