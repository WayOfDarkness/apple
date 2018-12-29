$('.tinymce').each(function () {
  var id = $(this).attr('id');
  initTinymce('#' + id);
});

$('.btn-create-update-photo').click(function () {
  var data = {};
  var box = $('.box-info-photo');
  box.find('.error').removeClass('error');

  data.gallery_id = $('input[name="gallery_id"]').val();
  data.title = box.find('input[name="title"]').val();
  data.status = $('select[name="status"]').val();
  data.description = tinyMCE.get('description').getContent();

  data.link_type = $('.search-link-autocomplete select[name="link_type"]').val();
  if (data.link_type == 'custom') data.link = $('.search-link-autocomplete input[name="link"]').val();
  else {
    data.link = $('.search-link-autocomplete input[name="link"]').attr('data-id');
    data.link_title = $('.search-link-autocomplete input[name="link"]').val();
  }

  // if (!data.title) {
  //   box.find('input[name="title"]').addClass('error');
  //   return toastr.error("Chưa nhập tiêu đề");
  // }

  data.image = $('input[name="image"]').val();
  if (!data.image) {
    return toastr.error("Chưa chọn hình ảnh");
  }

  data.multiLang = [];

  if (languages) {
    $.each(languages, function (index, elem) {
      var obj = {};
      obj.lang = elem;
      obj.id = $('input[name="translattion_' + elem + '"]').val()
      obj.title = $('input[name="title_' + elem + '"]').val();
      obj.description = tinyMCE.get("description_" + elem).getContent();
      data.multiLang.push(obj);
    });
  }


  $(this).addClass('disabled');
  var id = $(this).data('id');

  if (id) updatePhoto(id, data);
  else createPhoto(data);

});

function createPhoto(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/photo',
    data: data,
    success: function (res) {
      $(document).find('.disabled').removeClass('disabled');
      if (!res.code) {
        updateMetafield('photo', res.id);
        toastr.success("Tạo thành công");
        reloadPage('/admin/photo/' + res.id);
      }
    }
  });
}

function updatePhoto(id, data) {
  $(document).find('.disabled').removeClass('disabled');
  $.ajax({
    type: 'PUT',
    url: '/admin/photo/' + id,
    data: data,
    success: function (res) {
      if (!res.code) {
        updateMetafield('photo', id);
        toastr.success("Cập nhật thành công");
        reloadPage();
      }
    }
  });
}
