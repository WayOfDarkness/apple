$('.add-new-option').click(function () {
  var parent_id = $(this).data('parent_id');
  $('#modal-product-attribute').find('input[name="parent_id"]').val(parent_id);
  $('#modal-product-attribute').modal('show');
});

$('.edit-option').click(function () {
  var id = $(this).data('id');
  $.ajax({
    type: 'GET',
    url: '/admin/attribute/' + id,
    success: function (json) {
      if (!json.code) {
        var modal = $('#modal-product-attribute');
        modal.find('input[name="name"]').val(json.data.name);
        modal.find('.btn-admin').attr('data-id', json.data.id);
        modal.modal('show');
      } else toastr.error('Không tìm thấy');
    }
  })
});

$('#modal-product-attribute').on('hidden.bs.modal', function() {
  $(this).find('input').val('');
});

$('#modal-product-attribute').on('click', '.btn-admin', function () {
  var self = $(this);
  var modal = $('#modal-product-attribute');
  var id = $(this).attr('data-id');
  var data = {};
  data.name = modal.find('input[name="name"]').val();
  if (!data.name) {
    toastr.error("Chưa nhập tiêu đề");
    modal.find('input[name="name"]').addClass('error');
    return;
  }
  data.parent_id = modal.find('input[name="parent_id"]').val();

  self.addClass('disabled');

  if (typeof id === "undefined") {
    $.ajax({
      type: 'POST',
      url: '/admin/attribute',
      data: data,
      success: function (json) {
        self.removeClass('disabled');
        if (!json.code) {
          toastr.success('Tạo thành công');
          reloadPage();
        } else if (json.code == -1) {
          toastr.error("Thuộc tính đã tồn tại");
        }
      }
    });
  } else {
    $.ajax({
      type: 'PUT',
      url: '/admin/attribute/' + id,
      data: data,
      success: function (json) {
        self.removeClass('disabled');
        if (!json.code) {
          toastr.success('Cập nhật thành công');
          reloadPage();
        } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
      }
    });
  }
});

$('.delete-option').click(function () {
  var id = $(this).data('id');
  var card = $(this).closest('.clearfix');
  var cardName = card.find('.col-sm-4').first().text();
  popupConfirm("Xóa thuộc tính "+ cardName +"?", function (result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/attribute/' + id,
        success: function (json) {
          if (!json.code) {
            toastr.success('Xóa thuộc tính '+ cardName +' thành công');
            card.remove();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      })
    }
  });
})
