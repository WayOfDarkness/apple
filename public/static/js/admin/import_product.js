$('.btn-render-template').click(function () {
  var newWindow = window.open();
  $.get('/admin/import_product/template', function (res) {
    if (res.success) {
      newWindow.location = res.url;
    }
  }).fail(function (res) {
    toastr.error(res.error);
  });
});


$('.form-import-product').on('submit', function (e) {
  e.preventDefault();
  var file_data = $('#file').prop('files')[0];
  if (!file_data) {
    return toastr.error("Chưa chọn file sản phẩm");
  }
  var form_data = new FormData();
  form_data.append('file', file_data);
  $(this).find('button').addClass('disabled');
  $.ajax({
    type: 'POST',
    url: '/admin/import_product',
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    success: function (res) {
      if (res.success) {
        toastr.success("Nhập sản phẩm thành công");
        $(document).find('.disabled').removeClass('disabled');
      }
      reloadPage();
    },
    error: function (res) {
      toastr.error(res.error);
    }
  });
});


$('.form-import-image').on('submit', function (e) {
  e.preventDefault();
  var file_data = $('#file-zip').prop('files')[0];
  if (!file_data) {
    return toastr.error("Chưa chọn file zip");
  }
  var form_data = new FormData();
  form_data.append('file', file_data);
  $(this).find('button').addClass('disabled');
  $.ajax({
    type: 'POST',
    url: '/admin/api/uploadProductImage',
    data: form_data,
    cache: false,
    contentType: false,
    processData: false,
    success: function (res) {
      if (!res.code) {
        toastr.success("Nhập hình ảnh sản phẩm thành công");
        $(document).find('.disabled').removeClass('disabled');
        reloadPage();
      } else{
        toastr.error(res.message);
        $(document).find('.disabled').removeClass('disabled');
      }
    },
  });
});
