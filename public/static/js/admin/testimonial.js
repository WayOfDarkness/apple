initTinymce('#content');
initTinymce('#content_en');
initDataTable('table');
var modelName = 'testimonial';

$('.btn-create-update').click(function (event) {
  $(document).find('.error').removeClass('error');
  var data = {};
  data.name = $('input[name="name"]').val();
  data.content = tinyMCE.get('content').getContent();
  data.logo = $('input[name="logo"]').val();
  data.status = $('select[name="status"]').val();
  data.priority = parseInt($('input[name="priority"]').val());

  if (!data.name.trim().length) {
    toastr.error('Chưa nhập tên đối tác');
    $('input[name="name"]').addClass('error');
    return;
  }

  data.multiLang = [];
  if (languages) {
    $.each(languages, function (index, elem) {
      var obj = {};
      obj.lang = elem;
      obj.id = $('input[name="translattion_' + elem + '"]').val()
      obj.name = $('input[name="name_' + elem + '"]').val();
      obj.content = tinyMCE.get("content_" + elem).getContent();
      data.multiLang.push(obj);
    });
  }

  $(this).addClass('disabled');

  var id = $(this).data('id');
  if (id) updateTestimonial(id, data);
  else createTestimonial(data);
});

function createTestimonial(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/testimonial',
    data: data,
    success: function (json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        updateMetafield('testimonial', json.id);
        toastr.success('Tạo thành công');
        reloadPage('/admin/testimonial/' + json.id);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateTestimonial(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/testimonial/' + id,
    data: data,
    success: function (json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        toastr.success('Cập nhật thành công');
        updateMetafield('testimonial', id);
        reloadPage();
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}
