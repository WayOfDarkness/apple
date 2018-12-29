initDataTable('table');
var modelName = 'collection'

$('.tinymce').each(function () {
  var id = $(this).attr('id');
  initTinymce('#' + id);
});

$('.btn-create-update').click(function() {
  var id = $(this).data('id');
  var self = $(this);
  $('input').removeClass('error');
  var data = {};
  data.parent_id = $('select[name="parent_id"]').val();
  data.title = $('input[name="title"]').val();
  data.handle = $('input[name="handle"]').val();
  data.description = $('textarea[name="description"]').val();
  data.content = tinyMCE.get('content').getContent();
  data.image = $('input[name="image"]').val();
  data.status = $('select[name="status"]').val();
  data.tags = $("input[name='tags']").tagsinput('items');
  data.template = $('select[name="template"]').val() || '';

  if(!data.title) {
    $('input[name="title"]').addClass('error');
    return toastr.error('Chưa nhập tiêu đề');
  }
  if(!data.handle) {
    $('input[name="handle"]').parent().addClass('error');
    return toastr.error('Chưa nhập đường dẫn');
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

  self.addClass('disabled');
  if (id) updateCollection(id, data);
  else createCollection(data);
});

function createCollection(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/collection',
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        toastr.success('Tạo thành công');
        updateSEO('collection', json.id);
        updateMetafield('collection', json.id);
        reloadPage('/admin/collection/' + json.id);
      } else if (json.code == -1) {
        toastr.error('Nhóm sản phẩm đã tồn tại');
      } else if (json.code == -4) {
        toastr.error(json.message);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateCollection(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/collection/' + id,
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        updateSEO('collection', id);
        updateMetafield('collection', id);
        toastr.success('Cập nhật thành công');
        reloadPage();
      } else if (json.code == -1) {
        toastr.error('Nhóm sản phẩm đã tồn tại');
      } else if (json.code == -4) {
        toastr.error(json.message);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}


$(document).on('click', '.btn-remove', function() {
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  popupConfirm("Xóa nhóm sản phẩm?", function(result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/collection/' + id,
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

$(document).on('click','.sort-product',function () {
  var self = $(this);
  var li = self.closest('li');
  var id = self.data('id');
  $.ajax({
    type: 'POST',
    url: '/admin/api/collection/sortProduct/' + id,
    success: function (code) {
      if (!code){
        toastr.success('Sắp xếp thành công');
        li.prependTo(self.closest('ul'));
        li.find('a').addClass('red');
        setTimeout(function () {
          li.find('a').removeClass('red');
        },3000)
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  })
})

$(document).on('click','.btn-remove-product-icon',function () {
  var self = $(this);
  var li = self.closest('li');
  var product_id = self.data('id');
  var productName = li.find('a').text();
  var collection_id = $('.btn-create-update').data('id');
  popupConfirm('Bạn có muốn xóa sản phẩm ' + productName + ' khỏi nhóm sản phẩm không?',function (result) {
    if (result){
      $.ajax({
        type: 'DELETE',
        url: '/admin/api/collection/removeProduct',
        data:{
          'product_id' : product_id,
          'collection_id' : collection_id
        },
        success: function (code) {
          if (!code){
            toastr.success('Xóa sản phẩm ' + productName + ' khỏi nhóm thành công');
            li.remove();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      })
    }
  })
})

var tab_content_width = $('.listDragProduct').parent().width() - 50;
$('.listDragProduct').find('.title').css('width', tab_content_width);

$(".space").sortable({
  connectWith: '.space',
  tolerance: 'intersect',
  update: function(event, ui) {
    updatePriority();
  }
});

function updatePriority() {
  var listProduct = [];
  var collectionID = parseInt($('.listDragProduct').data('id'));
  $('.listDragProduct.ui-sortable').find('li').each(function(index, element) {
    var id = parseInt($(element).attr('data-id'));
    listProduct.unshift(id);
  });
  $.ajax({
    type: 'PUT',
    url: '/admin/api/productCollection/updatePriority',
    data: {
      listProduct: listProduct,
      collectionID: collectionID
    },
    success: function(res) {
      console.log(res);
    }
  });
}

$(".space").disableSelection();
