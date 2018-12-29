initTinymce('#description');
initDataTable('table');
var addProduct = '';
var modelName = 'sale';

$(".chosen-select-product").chosen({
  no_results_text: "Không tìm thấy sản phẩm"
});
$(".chosen-select").chosen({
  width: "100%"
});

$(document).on('click', '.btn-remove-product', function() {
  $(this).closest('.row').remove();
})

$('.btn-add-product').click(function() {
  var tags = $("input[name='tags']").tagsinput('items');
  var type = $('.chosen-type').val();
  if (tags.length) {
    addTagProduct(tags, type);
  } else {
    addNewProduct(type);
  }

})

$('.btn-create-update').click(function() {
  var id = $(this).data('id');
  var data = {};
  data.title = $('input[name="title"]').val();
  var product = [];
  $('.chosen-select-product').each(function(t, number) {
    if ($(this).val()) {
      product.push($(this).val());
    }
  })
  data.products = product;
  data.description = tinyMCE.get('description').getContent();
  data.value = $('input[name="value"]').val();
  data.type = $('select[name="type"]').val();
  data.start_date = $('input[name="start_date"]').val();
  data.end_date = $('input[name="end_date"]').val();
  data.status = $('select[name="status"]').val();
  data.type_relation = $('select[name="type_relation"]').val();

  if (!data.start_date) {
    $('input[name="start_date"]').addClass('error');
    return toastr.error('Chưa nhập ngày bắt đầu');
  }

  if (!data.end_date) {
    $('input[name="end_date"]').addClass('error');
    return toastr.error('Chưa nhập ngày kết thúc');
  }

  if (!data.title) {
    $('input[name="title"]').addClass('error');
    return toastr.error('Chưa nhập tiêu đề');
  }

  if (!data.value) {
    $('input[name="value"]').addClass('error');
    return toastr.error("Chưa nhập giá trị");
  }

  data.start_date = dmy2ymd(data.start_date);
  data.end_date = dmy2ymd(data.end_date);

  if (new Date(data.end_date) < new Date(data.start_date)) {
    $('input[name="end_date"]').addClass('error');
    return toastr.error('Ngày kết thúc không được bé hơn ngày bắt đầu');
  }

  $(this).addClass('disabled');
  if (id) updateSale(id, data);
  else createSale(data);
})

function addTagProduct(tags, type) {
  $.ajax({
    type: "POST",
    url: "/admin/api/sale/getproductFromTag",
    data: {
      tags: tags,
      type: type
    },
    success: function(json) {
      if (!json.code) {
        var html = tmpl('sale-item-product-content', json);
        var productHtml = '';
        json.id.forEach(function(element, index) {
          if (!checkExist(element.id)) {
            var obj = {
              id: element.id,
              title: element.title
            };
            productOption = tmpl('sale-item-product-selected', obj);
            productHtml += html.substring(0, html.indexOf('<select')) + productOption + html.substring(html.indexOf('</div>'), html.length);
          }
        })
        $('.list-product-content').append(productHtml);
        $(".chosen-select-product").chosen({
          no_results_text: "Không tìm thấy sản phẩm"
        });
      } else if (json.code == -2) {
        if (type == 'product') {
          toastr.error('Không có sản phẩm nào được gắn nhãn này');
        } else {
          toastr.error('Không có nhóm sản phẩm nào được gắn nhãn này');
        }
      }
    }
  })
}

function addNewProduct(type) {
  // if (addProduct == '') {
    $.ajax({
      type: "GET",
      url: "/admin/api/sale/getproduct?type=" + type,
      success: function(json) {
        if (!json.code) {
          var html = tmpl('sale-item-product-content', json);
          var option = '';
          console.log(json);
          json.id.forEach(function(element, index) {
            var obj = {
              id: element.id,
              title: element.title
            };
            option += tmpl('sale-item-product-option', obj);
          })
          addProduct = html.substring(0, html.indexOf('</select>')) + option + html.substring(html.indexOf('</select>'), html.length);
          $('.list-product-content').append(addProduct);
          $(".chosen-select-product").chosen({
            no_results_text: "Không tìm thấy sản phẩm"
          });
        }
      }
    })
  // } else {
  //   $('.list-product-content').append(addProduct);
  //   $(".chosen-select-product").chosen({
  //     no_results_text: "Không tìm thấy sản phẩm"
  //   });
  // }
}

function createSale(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/sale',
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        toastr.success('Tạo thành công');
        reloadPage('/admin/sale/' + json.id);
      } else if (json.code == -1) {
        toastr.error('Chương trình khuyến mãi đã tồn tại');
      } else if (json.code == -4) {
        toastr.error(json.message);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateSale(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/sale/' + id,
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        toastr.success('Sửa thành công');
        reloadPage();
      } else if (json.code == -1) {
        toastr.error('Chương trình khuyến mãi đã tồn tại');
      } else if (json.code == -4) {
        toastr.error(json.message);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

$('.btn-remove').click(function() {
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  popupConfirm("Xóa chương trình khuyến mãi", function(result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/sale/' + id,
        success: function(json) {
          if (!json.code) {
            toastr.success('Xóa chương trình khuyến mãi thành công');
            tbl.row(tr).remove().draw();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      });
    }
  })
})

$(document).on('change', '.form-control.chosen-select-product', function() {
  var self = $(this);
  var count = 0;
  var thisProductId = $(this).val();
  var selectProduct = $(this).closest(".form-group").find(".chosen-select-product");
  selectProduct.each(function() {
    if (thisProductId == $(this).val()) count++;
  });
  if (count > 1) {
    toastr.error("Sản phẩm này đã được thêm");
    self.val(0);
    self.trigger("chosen:updated");
  }
});

function checkExist(id) {
  var count = 0;
  var selectProduct = $('.list-product-content').closest(".form-group").find(".chosen-select-product");
  selectProduct.each(function() {
    if (id == $(this).val()) count++;
  });
  if (count > 0) {
    return 1;
  }
  return 0;
}

$('.list-tags').on('click', '.tag-name', function() {
  var text = $(this).html();
  $("input[name='tags']").tagsinput('add', text);
});

$('select[name="type"]').on('change', function() {
  var value = $(this).val();
  if (value == "percent") {
    $('input[name="value"]').addClass("input-percent");
    $('input[name="value"]').val("");
  } else {
    $('input[name="value"]').removeClass("input-percent");
    $('input[name="value"]').val("");
  }
})

$(document).on('keyup', '.input-percent', function() {
  var input = $('input[name="value"]').val();
  if (input < 0 || input > 100) {
    $('input[name="value"]').val(100);
    toastr.error(' Giá trị nằm trong khoảng 0% đến 100%');
  }
  return;
});
