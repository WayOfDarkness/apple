initDataTable('table');
var modelName = 'gallery';

$('.tinymce').each(function () {
  var id = $(this).attr('id');
  initTinymce('#' + id);
});

var currentPhoto;

$('.list-photos').on('click', '.item-photo .remove', function() {
  var id = $(this).data('id');
  var self = $(this);
  if (id) {
    $.ajax({
      type: 'DELETE',
      url: '/admin/photo/' + id,
      success: function() {
        toastr.success("Đã xóa");
        self.closest('.item-photo').remove();
      }
    });
  } else {
    self.closest('.item-photo').remove();
  }
});


$('.btn-create-update-gallery').click(function() {
  var data = {};
  $('.box-info-gallery').find('.error').removeClass('error');
  data.title = $('.box-info-gallery input[name="title"]').val();
  data.handle = $('.box-info-gallery input[name="handle"]').val();
  data.description = tinyMCE.get('description').getContent();
  data.status = $('select[name="status"]').val();
  data.template = $('select[name="template"]').val() || '';
  data.parent_id = $('select[name="parent_id"]').val() || -1;

  if (!data.title) {
    $('.box-info-gallery input[name="title"]').addClass('error');
    return toastr.error("Chưa nhập tiêu đề");
  }

  if (!data.handle) {
    $('input[name="handle"]').addClass('error');
    return toastr.error("Chưa nhập đường dẫn");
  }

  data.multiLang = [];

  if (languages) {
    $.each(languages, function (index, elem) {
      var obj = {};
      obj.lang = elem;
      obj.id = $('input[name="translattion_' + elem + '"]').val()
      obj.title = $('.box-info-gallery input[name="title_' + elem + '"]').val();
      obj.handle = $('.box-info-gallery input[name="handle_' + elem + '"]').val() || data.handle;
      obj.description = tinyMCE.get("description_" + elem).getContent();
      data.multiLang.push(obj);
    });
  }

  $(this).addClass('disabled');

  var id = $(this).data('id');
  if (id) updateGallery(id, data);
  else createGallery(data);

});

function createGallery(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/gallery',
    data: data,
    success: function (res) {
      $(document).find('.disabled').removeClass('disabled');
      if (!res.code) {
        toastr.success("Tạo thành công");
        updateMetafield('gallery', res.id);
        reloadPage('/admin/gallery/' + res.id);
      } else if (res.code == -1) {
        return toastr.error("Gallery đã tồn tại");
      }
    }
  });
}

function updateGallery(id, data) {
  $(document).find('.disabled').removeClass('disabled');
  $.ajax({
    type: 'PUT',
    url: '/admin/gallery/' + id,
    data: data,
    success: function (res) {
      if (!res.code) {
        toastr.success("Cập nhật thành công");
        updateMetafield('gallery', id);
        reloadPage();
      }
    }
  });
}

$(document).on('click','.status-double',function () {
  var arrId = [];
  $('tbody input:checkbox:checked').each(function () {
      arrId.push($(this).val());
  })

  var numSelected = $('tbody input:checkbox:checked').length;
  popupConfirm('Bạn có muốn nhân đôi ' + numSelected + ' mục đã chọn không?', function (result) {
    if (result) {
      $.ajax({
        type: 'POST',
        url: '/admin/gallery/double',
        data: {
          type: modelName,
          arrId: arrId
        },
        success: function (json) {
          toastr.success("Nhân đôi thành công");
          location.reload();
        }
      })
    }
  })
})

$(document).on('click', '.btn-remove-checked', function() {
  var arrId = [];
  $('.list-photos input:checkbox:checked').each(function() {
    var id = $(this).closest('.item-photo').find('.group-button span').data('id');
    arrId.push(id);
  })
  var numSelected = $('.list-photos input:checkbox:checked').length;
  popupConfirm('Bạn có muốn xóa ' + numSelected + ' mục đã chọn không?', function (result) {
    if(result){
      $.each(arrId,function(k, v){
        $.ajax({
          type: 'DELETE',
          url: '/admin/photo/' + v,
          success: function() {
          }
        });
      })
      toastr.success("Đã xóa thành công");
      $('.list-photos input:checkbox:checked').closest('.item-photo').remove();
      $('.action-box').addClass('hidden')
      checkItem();
    }
  })
})


$(document).ready(function() {
    var $chkboxes = $('.list-photos input:checkbox');
    var lastChecked = null;

    $chkboxes.click(function(e) {
        if(!lastChecked) {
            lastChecked = this;
            return;
        }

        if(e.shiftKey) {
            var start = $chkboxes.index(this);
            var end = $chkboxes.index(lastChecked);

            $chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastChecked.checked);

        }

        lastChecked = this;
    });
})

$(document).on('click', 'input:checkbox', function () {
  if ($(this).hasClass('select-all')) {
    $('.list-photos').find('input:checkbox').prop('checked', $(this).is(':checked'));
  }
  if ($('.list-photos input:checkbox:checked').length) {
    $('.action-box-photo').removeClass('hidden');
    var numberOfChecked = $('.list-photos input:checkbox:checked').length;
    $('.num-select').html(' ' + numberOfChecked + ' ');
  } else $('.action-box-photo').addClass('hidden');
})
$(document).ready(function(){
  checkItem();
})
function checkItem(){
  if ($('.list-photos').children().length == 0) {
    $('.check-all-gallery').addClass('hidden');
    $('.check-all-gallery input:checkbox').prop('checked', false);
  }
  else {
    $('.check-all-gallery').removeClass('hidden');
  }
}

$(".list-photos").sortable({
  connectWith: '.space',
  tolerance: 'intersect',
  update: function(event, ui) {
    updatePriority();
  }
});

function updatePriority() {
  console.log("test");
  // return;
  var listPhoto = [];
  var galleryId = parseInt($('.list-photos').data('id'));
  $('.list-photos').find('.item-photo').each(function(index, element) {
    var id = parseInt($(element).attr('data-id'));
    listPhoto.unshift(id);
  });
  $.ajax({
    type: 'PUT',
    url: '/admin/api/gallery/updatePriority',
    data: {
      listPhoto: listPhoto,
      galleryId: galleryId
    },
    success: function(res) {
      console.log(res);
    }
  });
}
