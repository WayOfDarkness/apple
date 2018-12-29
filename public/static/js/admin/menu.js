initDataTable('table.tbl-menu');
var modelName = 'menu';

$('.action-box').on('mousedown', '.btn-remove-menu', function(e) {
  e.stopPropagation();
  var arrId = [];
  $('tbody input:checkbox:checked').each(function() {
    arrId.push($(this).val());
  })
  var message = "Xóa các menu đã chọn?";
  popupConfirm(message, function(result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        data: {
          arrId: arrId
        },
        url: '/admin/menu',
        success: function(json) {
          if (!json.code) {
            toastr.success('Đã xóa');
            reloadPage();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      });
    }
  })
});

$('#modal-create-navigation form').submit(function(e) {
  e.preventDefault();
  var data = $(this).serialize();
  $.post('/admin/menu', data, function(res) {
    if (!res.code) {
      toastr.success("Tạo menu thành công");
      reloadPage();
    }
  });
});

$('#modal-create-navigation').on('hidden.bs.modal', function() {
  $(this).find('input').val('');
});

$(document).on('click', '.btn-edit-navigation', function() {
  var id = $(this).data('id');
  $.get('/admin/api/menu/' + id, function(json) {
    if (!json.code) {
      $('#modal-edit-navigation').find('input[name="title"]').val(json.data.title);
      $('#modal-edit-navigation').find('form').attr('data-id', json.data.id);
      $('#modal-edit-navigation').modal('show');
    }
  });
});

var tab_content_width = $('.list-menu-child').parent().width() - 50;
$('.list-menu-child').find('.title').css('width', tab_content_width);

$(".space").sortable({
  connectWith: '.space',
  tolerance: 'intersect',
  receive: function(event, ui) {
    calcWidth($(this).siblings('.title'));
  },
  update: function(event, ui) {
    updatePriority();
  }
});

$(".space").each(function() {
  calcWidth($(this).siblings('.title'));

});

function calcWidth(obj) {

  $('.list-menu-child > li > .title').css('width', tab_content_width);

  var titles = $(obj).siblings('.space').children('.route').children('.title');
  $(titles).each(function(index, element) {
    var pTitleWidth = parseInt($(obj).css('width'));
    var leftOffset = parseInt($(obj).siblings('.space').css('margin-left'));
    var newWidth = pTitleWidth - leftOffset;
    $(element).css('width', newWidth);
    calcWidth(element);
  });
}

function updatePriority() {
  var listMenuChild = [];
  $('.list-menu-child.ui-sortable').find('li').each(function(index, element) {
    var id = parseInt($(element).attr('data-id'));
    var li = $(element).parent().closest('.ui-sortable-handle');
    if (li.length) {
      listMenuChild.push({
        id: id,
        parent_id: parseInt(li.attr('data-id'))
      });
    } else {
      listMenuChild.push({
        id: id,
        parent_id: parseInt($('.list-menu-child').data('id'))
      });
    }
  });
  $.ajax({
    type: 'PUT',
    url: '/admin/menu/updatePriority',
    data: {
      menu: listMenuChild
    },
    success: function(res) {
      console.log(res);
    }
  });
}

$(".space").disableSelection();

$('.btn-create-update-menu-child').click(function() {
  var id = $(this).attr('data-id');

  var modal = $(this).closest('.modal');
  modal.find('.error').removeClass('error');
  var data = {};
  data.parent_id = modal.find('select[name="parent_id"]').val();
  data.title = modal.find('input[name="title"]').val();
  data.image = modal.find('input[name="image"]').val();
  data.link_type = modal.find('select[name="link_type"]').val();
  data.status = modal.find('select[name="status"]').val();
  data.multiLang = [];
  if (languages) {
    $.each(languages, function(index, elem) {
      var obj = {};
      obj.lang = elem;
      obj.id = $('input[name="translattion_' + elem + '"]').val()
      obj.title = $('input[name="title_' + elem + '"]').val();
      if (id) {
        obj.title = $('#modal-edit-menu-child input[name="title_' + elem + '"]').val();
      }
      data.multiLang.push(obj);
    });
  }

  if (data.link_type == 'custom') {
    var link = modal.find('input[name="link"]').val();
    var rex = link.match(/(\/{1}[a-z0-9\-\_\.]+)+/i);
    var rexLink = '/'
    if (rex) {
      rexLink = rex[0];
    }
    if (/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(link) || rexLink == link) {
      data.link = link;
      if (languages) {
        $.each(data.multiLang, function(index, elem) {
          elem.link = $('input[name="link_' + elem.lang + '"]').val();
        });
      }
    } else {
      modal.find('input[name="link"]').addClass('error');
      return toastr.error("Đường dẫn phải bắt đầu bằng dấu / hoặc http(s)://");
    }
  } else data.link = modal.find('input[name="link"]').attr('data-id');
  if (!data.title) {
    modal.find('input[name="title"]').addClass('error');
    return toastr.error("Chưa nhập tiêu đề");
  }

  if (id) updateMenuChild(id, data);
  else createMenuChild(data);
});

function createMenuChild(data) {
  $.post('/admin/menu', data, function(res) {
    if (!res.code) {
      toastr.success("Tạo menu thành công");
      reloadPage();
    }
  });
}

function updateMenuChild(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/menu/' + id,
    data: data,
    success: function(res) {
      if (!res.code) {
        toastr.success("Cập nhật thành công");
        reloadPage();
      }
    }
  });
}

$('.group-edit-button').on('click', '.delete', function() {
  var id = $(this).data('id');
  var self = $(this);
  var nameMenu = $(this).closest('.title').find('span').first().text();
  popupConfirm("Xóa menu " + nameMenu, function(confirm) {
    if (confirm) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/menu/' + id,
        success: function(res) {
          if (!res.code) {
            toastr.success("Đã xóa menu " + nameMenu);
            self.closest('li').remove();
            reloadPage();
          }
        }
      });
    }
  });
});
$('.group-edit-button').on('change', '.status input', function() {
  $(this).prop('disabled', true);
  var id = $(this).parent().data('id');
  var self = $(this);
  var status = $(this).is(':checked') ? $(this).val(): 'inactive';
  var arrId = [];
  arrId.push(id);
  $.ajax({
    type: 'POST',
    url: '/admin/api/updateStatus',
    data: {
      type: 'menu',
      arrId: arrId,
      status: status
    },
    success: function (json) {
      self.prop('disabled', false);
    }
    })
});

$('.group-edit-button').on('click', '.edit', function() {
  var id = $(this).data('id');
  $.ajax({
    type: 'GET',
    url: '/admin/api/menu/' + id + '/detail',
    success: function(res) {
      if (!res.code) {
        var modal = $('#modal-edit-menu-child');
        modal.find('select[name="parent_id"] option').prop('disabled', false);
        modal.find('.item-choose-image').removeClass('active');
        modal.find('.btn-create-update-menu-child').attr('data-id', id);
        modal.find('select[name="parent_id"]').val(res.menu.parent_id);
        modal.find('select[name="parent_id"] option[value="' + id + '"]').prop('disabled', true);
        modal.find('input[name="title"]').val(res.menu.title);
        modal.find('select[name="link_type"]').val(res.menu.link_type);
        modal.find('select[name="status"]').val(res.menu.status);
        if (res.menu.link_type == 'custom') {
          modal.find('input[name="link"]').val(res.menu.link);
        } else {
          modal.find('select[name="link_type"]').trigger('change');
          if (res.menu.link_obj) {
            modal.find('input[name="link"]').val(res.menu.link_obj.title);
          }
          modal.find('input[name="link"]').attr('data-id', res.menu.link);
        }
        if (res.menu.image) {
          modal.find('.item-choose-image').addClass('active');
          modal.find('input[name="image"]').val(res.menu.image);
          modal.find('img').attr('src', '/uploads/' + resizeImage(res.menu.image, 480));
        }

        if (languages) {
          var translations = res.translations;
          $.each(translations, function(index, elem) {
            modal.find('input[name="title_' + elem.lang + '"]').val(elem.title);
            modal.find('input[name="translattion_' + elem.lang + '"]').val(elem.id);
          });
        }

        modal.modal("show");
      }
    }
  });
});

$('.btn-update-parent').click(function() {
  var id = $(this).data('id');
  var data = {};
  data.title = $('.parent-info input[name="title"]').val();
  data.image = $('.parent-image input[name="image"]').val();
  updateMenuChild(id, data);
});
