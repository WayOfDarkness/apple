initDataTable('table');
var modelName = 'page';

$('.tinymce').each(function () {
  var id = $(this).attr('id');
  initTinymce('#' + id);
});

$('.btn-create-update').click(function() {
  $(document).find('.error').removeClass('error');
  var data = {};
  data.title = $('input[name="title"]').val();
  data.handle = $('input[name="handle"]').val();
  data.image = $('input[name="image"]').val();
  data.description = $('textarea[name="description"]').val();
  data.content = tinyMCE.get('content').getContent();
  data.status = $('select[name="status"]').val();
  data.tags = $("input[name='tags']").tagsinput('items');
  data.template = $('select[name="template"]').val() || '';

  if(!data.title.trim().length) {
    toastr.error('Chưa nhập tiêu đề');
    return $('input[name="title"]').addClass('error');
  }

  if(!data.handle) {
    toastr.error('Chưa nhập đường dẫn');
    return $('input[name="handle"]').parent().addClass('error');
  }

  data.multiLang = [];

  if (languages) {
    $.each(languages, function (index, elem) {
      var obj = {};
      obj.lang = elem;
      obj.id = $('input[name="translattion_' + elem + '"]').val()
      obj.title = $('input[name="title_' + elem + '"]').val();
      obj.handle = $('input[name="handle_' + elem + '"]').val() || data.handle;
      obj.description = $('textarea[name="description_' + elem + '"]').val();
      obj.content = tinyMCE.get("content_" + elem).getContent();
      data.multiLang.push(obj);
    });
  }

  $(this).addClass('disabled');

  var id = $(this).data('id');
  if (id) updatePage(id, data);
  else createPage(data);
});

function createPage(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/page',
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        toastr.success('Tạo thành công');
        updateSEO('page', json.id);
        updateMetafield('page', json.id);
        setTimeout(function(){},5000);
        reloadPage('/admin/page/' + json.id);
      } else if (json.code == -1) {
        toastr.error('Trang đã tồn tại');
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updatePage(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/page/' + id,
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        toastr.success('Cập nhật thành công');
        updateSEO('page', id);
        updateMetafield('page', id);
      } else if (json.code == -1) {
        toastr.error('Trang đã tồn tại');
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
  			url: '/admin/page/' + id,
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
