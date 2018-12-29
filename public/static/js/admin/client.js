initTinymce('#description');
initDataTable('table');
var modelName = 'client';

$('.btn-create-update').click(function (event) {
  $(document).find('.error').removeClass('error');
  var data = {};
  data.name = $('input[name="name"]').val();
  data.address = $('input[name="address"]').val();
  data.phone = $('input[name="phone"]').val();
  data.fax = $('input[name="fax"]').val();
  data.website = $('input[name="website"]').val();
  data.description = tinyMCE.get('description').getContent();
  data.logo = $('input[name="logo"]').val();
  data.status = $('select[name="status"]').val();
  data.priority = parseInt($('input[name="priority"]').val());

  if (!data.name.trim().length) {
    toastr.error('Chưa nhập tên');
    $('input[name="name"]').addClass('error');
    return;
  }

  $(this).addClass('disabled');
  var id = $(this).data('id');
  if (id) updateClient(id, data);
  else createClient(data);
});

function createClient(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/client',
    data: data,
    success: function (json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        updateMetafield('client', json.id);
        toastr.success('Tạo thành công');
        reloadPage('/admin/client/' + json.id);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateClient(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/client/' + id,
    data: data,
    success: function (json) {
      console.log(json);
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        updateMetafield('client', json.id);
        toastr.success('Cập nhật thành công');
        reloadPage();
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}
