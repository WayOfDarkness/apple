// initDataTable('.table-list-product');
var modelName = 'product';

$(document).ready(function() {
    initDataTableAjax('.table-list-product', '/admin/api/getProductPaginate', '/admin/api/exportProductExcel');
});

$('.tinymce').each(function () {
  var id = $(this).attr('id');
  initTinymce('#' + id);
});

// $('.table-variant').dataTable({
//   bPaginate: false,
//   bFilter: false,
//   bLengthChange: false,
//   info: false,
//   ordering: false,
//   columnDefs: [
//     {
//       targets: 3,
//       className: "text-right",
//     },
//     {
//       targets: 4,
//       className: "text-right",
//     }]
//   });

var attributes;

$(".chosen-select").chosen({width: "100%"});

$('input[name="stock_manage"]').on('change', disableInventory);

disableInventory();

$('.btn-create-update').click(function() {
  $(document).find('.error').removeClass('error');
  var id = $(this).data('id');
  var type = $(this).data('type');
  var self = $(this);
  var data = {};
  data.title = $('input[name="title"]').val();
  data.handle = $('input[name="handle"]').val();
  data.description = $('textarea[name="description"]').val();
  data.content = tinyMCE.get('content').getContent();
  data.stock_manage = $('input[name="stock_manage"]').is(':checked');
  data.stop_selling = $('select[name="stop_selling"]').val();
  data.stock_manage = data.stock_manage ? 1 : 0;
  data.price = strToPrice($('input[name="product_price"]').val());
  data.price_compare = strToPrice($('input[name="product_price_compare"]').val());

  if (!data.title) {
    toastr.error("Chưa nhập tiêu đề");
    return $('input[name="title"]').addClass('error');
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
      obj.title = $('input[name="title_' + elem + '"]').val();
      obj.handle = $('input[name="handle_' + elem + '"]').val() || data.handle;
      obj.description = $('textarea[name="description_' + elem + '"]').val();
      obj.content = tinyMCE.get("content_" + elem).getContent();
      data.multiLang.push(obj);
    });
  }

  if (!$('.list-all-variant .variant-item').length && !$('.table-variant').length) {
    if (data.price === "") {
      console.log("Test");
      $('input[name="product_price"]').addClass('error');
      return toastr.error("Chưa nhập giá sản phẩm");
    }
  } else {
    var variants = $('.list-all-variant .variant-item');
    for (var i = 0; i < variants.length; i++) {
      if (variants.eq(i).find('input[name="variant-price"]').val() === "") {
        variants.eq(i).find('input[name="variant-price"]').addClass('error');
        return toastr.error("Chưa nhập giá cho phiên bản");
      }
      if (data.stock_manage && !variants.eq(i).find('input[name="variant-inventory"]').val()) {
        variants.eq(i).find('input[name="variant-inventory"]').addClass('error');
        return toastr.error("Chưa nhập tồn kho");
      }
    }
  }

  data.arrOption = [];
  // $('.list-options .item-option').each(function(index, element) {
  //   if ($(this).find('.chosen-select').val().length) {
  //     data.arrOption[index] = $(this).find('.control-label').attr('data-id');
  //   }
  // });
  $('.list-attributes .item-attribute').each(function (index, elem) {
    var option_value = $(this).find('select').val();
    if (option_value) {
      data.arrOption[index] = option_value;
    }
  });

  data.image = '';

  if ($('.featured-image.value').val()) {
    data.image = $('.featured-image.value').val();
  }

  if ($('.active-feauture-image.active').length) {
    data.image = $('.active-feauture-image.active').parent().attr('data-name');
  }

  if (!data.image && $('.product-images .list-image .image').length) {
    data.image = $('.product-images .list-image .image').first().attr('data-name');
  }

  data.listImage = [];
  $('.product-images').find('.list-image .image').each(function () {
    data.listImage.push($(this).attr('data-name'));
  });
  data.collections = $('.chosen-collection').val();
  data.tags = $("input[name='tags']").tagsinput('items');
  data.priority = $("input[name='priority']").val();
  data.status = type?'inactive':$('select[name="status"]').val();
  data.template = $('select[name="template"]').val() || '';

  if (data.stop_selling == 'availableSoon' || data.stop_selling == 'publishSoon') {

    var date_publish = $('input[name="publish_date"]').val();
    var time_publish = $('input[name="publish_time"]').val();
    if (!date_publish || !time_publish) {
      return toastr.error("Chưa chưa chọn thời gian phát hành.");
    }

    date_publish = dmy2ymd(date_publish);
    var now = new Date();
    var publish_time = new Date(date_publish + ' ' + time_publish);
    if (publish_time < now) {
      return toastr.error("Thời gian phát hành phải lớn hơn hiện tại .");
    }
  }
  self.addClass('disabled');
  if (id) updateProduct(id, data);
  else createProduct(data, type);
});

function createProduct(data, type) {
  if (type) var newWindow = window.open();
  $.ajax({
    type: 'POST',
    url: '/admin/product',
    data: data,
    success: function(json) {
      if (json.code == -3) {
        toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        $(document).find('.btn-create-update').removeClass('disabled');
      } else if (json.code == -4) {
        toastr.error(json.message);
        $(document).find('.btn-create-update').removeClass('disabled');
      } else {
        var product_id = json.id;

        addProductBuyTogether(product_id);
        updateSEO('product', product_id);
        updateMetafield('product', product_id);
        // updateMetafield('product_attribute', product_id);

        var list_variant = $('.list-all-variant .variant-item');
        if (!list_variant.length) {
          var dataVariant = {};
          var obj = {
            title: 'Default',
            options: '',
            product_id: product_id,
            price: data.price,
            price_compare: data.price_compare,
            stock_quant: 1,
            status: 'active',
            list_image: ''
          };
          dataVariant[0] = obj;

          $.post('/admin/variant', dataVariant, function (json) {
            if (!json.code) {
              updateProductStock(product_id);
              if (type){
                $.get('/admin/api/get_url?type=product&id=' + product_id, function (json) {
                  newWindow.location = json.product.url;
                });
              }
              reloadPage('/admin/product/' + product_id);
            } else {
              toastr.error(json.message);
            }
          });

        } else {
          createVariant(list_variant, product_id);
        }
      }
    }
  });
}

function updateProduct(id, data) {
  var list_variant = $('.list-all-variant .variant-item');
  var checkNumVariant = 0;
  list_variant.each(function(index, el) {
    var self = $(this);
    if (self.find('input[name="check-variant"]').is(':checked')) {
      checkNumVariant = 1;
    }
  });
  if (!checkNumVariant) {
    $('.btn-create-update').removeClass('disabled');
    toastr.error('Vui lòng chọn ít nhất một phiên bản');
    return;
  }
  $.ajax({
    type: 'PUT',
    url: '/admin/api/product/' + id,
    data: data,
    success: function(json) {
      if (json.code == -3) {
        toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        $(document).find('.btn-create-update').removeClass('disabled');
      } else if (json.code == -4) {
        toastr.error(json.message);
        $(document).find('.btn-create-update').removeClass('disabled');
      } else {
        // toastr.success("Cập nhật thành công");
        updateSEO('product', id);
        updateMetafield('product', id);
        updateMetafield('product_attribute', id);
        updateProductStock(id);
        updateVariant(list_variant, id);

        if ($('.add-product-buy-together').find('.row').length) {
          addProductBuyTogether(id, reload_page = true);
        } else {
          setTimeout(function(){
            reloadPage();
          }, 1000);
        }
      }
    }
  });
}

function createVariant(listVariant, product_id) {
  var data = {};
  listVariant.each(function(index, el) {
    var obj = {};
    var variant = $(this);
    obj.product_id = product_id;
    obj.title = variant.find('input[name="variant-title"]').val();
    obj.options = variant.find('input[name="variant-option"]').val();
    if (obj.options) obj.options = JSON.parse(obj.options);
    obj.price = variant.find('input[name="variant-price"]').val() || $('input[name="product_price"]').val();
    obj.price =  strToPrice(obj.price);
    obj.price_compare = variant.find('input[name="variant-price-compare"]').val() || $('input[name="product_price_compare"]').val();
    obj.price_compare = strToPrice(obj.price_compare);
    obj.stock_quant = variant.find('input[name="variant-inventory"]').val();
    obj.status = variant.find('input[name="check-variant"]').is(':checked') ? 'active' : 'inactive';
    if (!$('input[name="stock_manage"]').is(':checked') && !obj.stock_quant) obj.stock_quant = 1;
    obj.list_image = [];
    variant.find('.list-image .image').each(function () {
      obj.list_image.push($(this).attr('data-name'));
    });
    data[index] = obj;
  });

  $.post('/admin/variant', data, function (json) {
    if (!json.code) {
      $(document).find('.btn-create-update').removeClass('disabled');
      updateProductStock(product_id);
      toastr.success("Tạo sản phẩm thành công");
      reloadPage('/admin/product/' + product_id);
      return false;
    } else toastr.error(json.message);
  });
}

function updateVariant(listVariant, productID) {
  var data = {};
  listVariant.each(function(index, el) {
    var obj = {};
    var variant = $(this);
    obj.id = variant.data('id');
    obj.title = variant.find('input[name="variant-title"]').val();
    // obj.options = variant.find('input[name="variant-option"]').val();
    // obj.options = obj.options.split('/');
    obj.price = variant.find('input[name="variant-price"]').val() || $('input[name="product_price"]').val();
    obj.price =  strToPrice(obj.price);
    obj.price_compare = variant.find('input[name="variant-price-compare"]').val() || $('input[name="product_price_compare"]').val();
    obj.price_compare = strToPrice(obj.price_compare);
    obj.stock_quant = variant.find('input[name="variant-inventory"]').val();
    obj.status = variant.find('input[name="check-variant"]').is(':checked')?'active':'inactive';
    if (!$('input[name="stock_manage"]').is(':checked') && !obj.stock_quant) obj.stock_quant = 1;
    obj.list_image = [];
    variant.find('.list-image .image').each(function () {
      obj.list_image.push($(this).attr('data-name'));
    });
    data[index] = obj;
  });

  $.ajax({
    url: '/admin/variant',
    method: 'PUT',
    data: data,
    success: function (json) {
      if (!json.code) {
        $(document).find('.btn-create-update').removeClass('disabled');
        updateProductStock(productID);
        toastr.success("Câp nhập sản phẩm thành công");
        return false;
      } else toastr.error(json.message);
    }
  });
}

function addProductBuyTogether(product_id, reload_page = false) {
  var data = [];
  $('.add-product-buy-together').find('.row').each(function (index, value) {
    var obj = {
      product_buy_together_id: $(this).attr('data-product_buy_together_id'),
      product_buy_together_title: $(this).attr('data-title'),
      price_sale: $(this).attr('data-price_sale'),
      promotion: $(this).attr('data-promotion'),
      status: $(this).attr('data-status')
    };
    data.push(obj);
  });

  $.ajax({
    type: 'POST',
    url: '/admin/product_buy_together',
    data: {
      data: data,
      product_id: product_id
    },
    success: function (json) {
      if (reload_page) {
        reloadPage();
      }
    }
  })
}

// $('.btn-create-update-variant').click(function() {
//   var modal = $(this).closest('.modal');
//   modal.find('.error').removeClass('error');
//   var data = {};
//   data.product_id = modal.find('input[name="product_id"]').val();
//   data.title = modal.find('input[name="title"]').val();
//
//   data.price = modal.find('input[name="price"]').val();
//   data.price_compare = modal.find('input[name="price_compare"]').val();
//   data.price = strToPrice(data.price);
//   data.price_compare = strToPrice(data.price_compare);
//   data.stock_quant = modal.find('input[name="variant_inventory"]').val();
//   data.status = 'active';
//   data.options = [];
//   modal.find('.input-option').each(function () {
//     data.options.push($(this).val());
//   });
//
//   if (!data.price) {
//     modal.find('input[name="price"]').addClass('error');
//     return toastr.error("Chưa nhập giá phiên bản");
//   }
//
//   if (!$('input[name="stock_manage"]').is(':checked') && !data.stock_quant) data.stock_quant = 1;
//
//   data.list_image = [];
//   modal.find('.list-image .image').each(function() {
//     data.list_image.push($(this).attr('data-name'));
//   });
//
//   var variant_id = $(this).data('id');
//   if (variant_id) updateItemVariant(variant_id, data);
//   else createItemVariant(data);
//
// });



$('.edit-variant').click(function() {
  var id = $(this).data('id');
  var modal = $('#modal-edit-variant');
  $.get('/admin/variant/' + id, function (json) {
    if (!json.code) {
      var data = json.data;
      modal.find('input').val('');
      modal.find('.list-image .image').remove();
      for (i = 1; i <= 6; i++){
        modal.find('select[name="option_' + i + '"]').attr('data-value', data['option_' + i]);
      }
      modal.find('input[name="title"]').val(data.title);
      modal.find('input[name="price"]').val(numToStringPrice(data.price));
      modal.find('input[name="price_compare"]').val(numToStringPrice(data.price_compare));

      var check_stock_manage = $('input[name="stock_manage"]').is(':checked');
      if (check_stock_manage) {
        modal.find('input[name="variant_inventory"]').val(data.stock_quant);
        modal.find('input[name="variant_inventory"]').prop('disabled', false);
      } else {
        modal.find('input[name="variant_inventory"]').val(1);
        modal.find('input[name="variant_inventory"]').prop('disabled', true);
      }
      data.list_image.forEach(function (element, index) {
        var obj = {
          name: element.name,
          src: '/uploads/' + resizeImage(element.name, 480)
        };
        var item_image = tmpl("add-item-image", obj);
        modal.find('.add-image').before(item_image);
      });
      modal.find('.btn-create-update-variant').attr('data-id', data.id);
      modal.modal('show');
    }
  })
})

$(document).on('click', '.btn-rotate-image', function(e){
  e.stopPropagation();
  var img = $(this).parent().find('img');
  var src = $(img).attr('src');
  src = src.replace('_240', '');
  var filename = src.split('/').pop();
  filename = filename.split('?')[0];
  $.get('/admin/api/rotate?filename=' + filename, function(res) {
    var timestamp = new Date() - 0;
    $(img).attr('src', '/uploads/' + resizeImage(filename, 480) + '?v=' + timestamp);
  });
});

$(document).on('click', '.btn-remove-product', function() {
  var id = $(this).attr('data-id');
  var tr = $(this).closest('tr');
  if(confirm("Xóa sản phẩm" + tr.find('td:first-child a').html() + " ?")) {
    $.ajax({
      type: 'DELETE',
      url: '/admin/product/' + id,
      success: function(json) {
        if(!json.code) {
          toastr.success('Xóa sản phẩm '+tr.find('td:first-child a').html()+' thành công');
          tblProduct.row(tr).remove().draw();
        } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
      }
    });
  }
});

$(document).on('click', '.btn-remove-variant', function() {
  var itemVariant = $(this).closest('tr');
  var variant_id = $(this).attr('data-id');
  if(variant_id) deleteVariant(variant_id, itemVariant);
});

function deleteVariant(variant_id, row) {
  var nameVariant =  row.find('td:eq(2)').text();
  popupConfirm('Bạn có muốn xóa phiên bản '+ nameVariant +' không?',function (result) {
    if (result){
      $.ajax({
        type: 'DELETE',
        url: '/admin/variant/' + variant_id,
        success: function(json) {
          if(!json.code) toastr.success("Xóa thành công phiên bản " + nameVariant);
          row.remove();
        }
      });
    }
  })
}

//Variant hidden
$('.list-all-variant-edit').on('change', 'input[name="check-variant"]', function () {
  if (!$(this).is(':checked')) {
    $(this).closest('.variant-item').addClass('hidden');
    var variant_id = $(this).closest('.variant-item').data('id');
    var variant_title = $(this).closest('.variant-item').find('input[name="variant-option"]').val();
    variant_title = variant_title ? variant_title : $(this).closest('.variant-item').find('input[name="variant-title"]').val();
    $('#modal-add-variant select[name="title"]').append(`<option value=${variant_id}>${variant_title}</option>`);
    checkVariantcreate();
  }
});

$('.btn-create-update-variant').click(function() {
  var modal = $(this).closest('.modal');
  var id = modal.find('select[name="title"]').val();
  if (id) {
    var variant =  $('[variant-id='+ id +']');
    variant.removeClass('hidden');
    variant.find('.checkmark').click();

    modal.modal('hide');
    modal.find('option[value="'+ id +'"]').remove();
    checkVariantcreate();
  }
});

function checkVariantcreate(){
  var variant_hidden_count = $('#modal-add-variant select[name="title"] option').length;
  if (variant_hidden_count == 1) {
    $('.btn-add').addClass('hidden');
  }
  else {
    $('.btn-add').removeClass('hidden');
  }
}


$(document).on('click', '.move-next', function(e) {
  e.stopPropagation();
  $(document).find('.moving').removeClass('moving');
  var item = $(this).closest('.image').addClass('moving');
  var index = $(document).find('.list-image').find(item).index();
  if($(document).find('.list-image').find('.image').eq(index+1).html()) {
    var html = $(document).find('.list-image').find(item).get(0).outerHTML;
    $(document).find('.list-image').find('.image').eq(index+1).after(html);
    item.remove();
  }
});

$(document).on('click', '.move-prev', function(e) {
  e.stopPropagation();
  $(document).find('.moving').removeClass('moving');
  var item = $(this).closest('.image').addClass('moving');
  var index = $(document).find('.list-image').find(item).index();
  if(index) {
    if($(document).find('.list-image').find('.image').eq(index-1).html()) {
      var html = $(document).find('.list-image').find(item).get(0).outerHTML;
      $(document).find('.list-image').find('.image').eq(index-1).before(html);
      item.remove();
    }
  }
});


function updateProductStock(product_id) {
  $.get('/admin/product/updateStock?product_id=' + product_id, function(json) {
    if (!json.code) console.log("Update stock");
  });
}

$(document).on('click', '.list-all-variant span.remove', function() {
  $(this).parent().remove();
});

$(document).on('click', '.product-images span.remove', function() {
  $(this).parent().remove();
});

$(document).on('click','.btn-add-tag',function () {
    var data = $(this).closest('#modal-add-tag').find('input[name="tags"]').tagsinput('items');
    var arrId = [];
    $('tbody input:checkbox:checked').each(function () {
        arrId.push($(this).val());
    })
    $.ajax({
        type: "POST",
        url: '/admin/api/product/tag',
        data:{
          'data': data,
          'arrId': arrId
        },
        success: function (json) {
          $('#modal-add-tag').modal('hide');
          toastr.success('Thêm vào tag thành công');
        }
    })
});

$(document).on('click','.btn-remove-tag',function () {
    var data = $(this).closest('#modal-remove-tag').find('input[name="tags"]').tagsinput('items');
    var arrId = [];
    $('tbody input:checkbox:checked').each(function () {
      arrId.push($(this).val());
    });

    $.ajax({
      type: "DELETE",
      url: '/admin/api/product/tag',
      data: {
        'data': data,
        'arrId': arrId
      },
      success: function (json) {
        $('#modal-remove-tag').modal('hide');
        toastr.success('Xóa tag thành công');
      }
    });
});

$(document).on('click','.btn-add-collection',function () {
    var arrIdCollection = [];
    $('#modal-add-collection :checked').each(function () {
        arrIdCollection.push($(this).val());
    })
    var arrId = [];
    $('tbody input:checkbox:checked').each(function () {
        arrId.push($(this).val());
    })
    $.ajax({
        type: 'POST',
        url: '/admin/api/collection/addMuch',
        data:{
          'arrIdCollection': arrIdCollection,
          'arrId': arrId
        },
        success: function (json) {
          $('#modal-add-collection').modal('hide');
          toastr.success('Thêm vào nhóm sản phẩm thành công');
        }
    })
});

$(document).on('click','.btn-remove-collection',function () {
    var arrIdCollection = [];
    $('#modal-remove-collection :checked').each(function () {
        arrIdCollection.push($(this).val());
    })
    var arrId = [];
    $('tbody input:checkbox:checked').each(function () {
        arrId.push($(this).val());
    })
    $.ajax({
        type: 'DELETE',
        url: '/admin/api/collection/deleteMuch',
        data:{
            'arrIdCollection': arrIdCollection,
            'arrId': arrId
        },
        success: function (json) {
          $('#modal-remove-collection').modal('hide');
          toastr.success('Xóa khỏi nhóm sản phẩm thành công');
        }
    })
});

$(document).on('click', '.btn-create-product-buy-together', function() {
  var modal = $(this).closest('.modal')
  var data = {};
  data.product_buy_together_id = modal.find('select[name="product_buy_together"]').val();
  if (!data.product_buy_together_id) {
    modal.find('select[name="product_buy_together"]').addClass('error');
    return toastr.error('Chưa chọn sản phẩm mua kèm');
  }
  data.product_buy_together_title = modal.find('select[name="product_buy_together"] option:selected').text();
  data.price_sale = modal.find('input[name="price_sale"]').val();
  data.price_sale = strToPrice(data.price_sale);

  if (!isNaN(data.price_sale.length)) {
    modal.find('input[name="price_sale"]').addClass('error');
    return toastr.error('Chưa nhập giá sản phẩm mua kèm');
  }

  data.promotion = modal.find('input[name="promotion"]').val();
  data.status = modal.find('select[name="status"]').val();

  var id = $(this).attr('data-id');
  var product_id = $(this).attr('data-product_id');

  if (id) {
    $.ajax({
      type: 'PUT',
      url: '/admin/product_buy_together/' + id,
      data: data,
      success: function(res) {
        if (!res.code) {
          toastr.success("Cập nhật thành công");
          reloadPage();
        } else {
          toastr.error(res.message);
        }
      }
    });
  } else if (product_id) {
    data.product_id = product_id;
    $.ajax({
      type: 'POST',
      url: '/admin/product_buy_together/one',
      data: data,
      success: function (res) {
        if (!res.code) {
          toastr.success("Thêm sản phẩm thành công");
          reloadPage();
        } else {
          toastr.error(res.message);
        }
      }
    });
  } else {
    $.get('/admin/api/product/detail/' + data.product_buy_together_id, function (res) {
      if (!res.code) {
        $('.add-product-buy-together').append('' +
          '<div style="padding-left: 10px" class="row" data-product_buy_together_id="' + data.product_buy_together_id + '" data-price_sale="' + data.price_sale + '" data-promotion="' + data.promotion + '" data-status="' + data.status + '" data-title="' + data.product_buy_together_title + '">\n' +
          '  <div class="pull-left">' +
          '    <a class="product_buy_together">' + data.product_buy_together_title + '</a>' +
          '    <p style="margin-top: 5px;">' +
          '       <span class= "price_sale" > ' + formatMoney(data.price_sale) + '</span > ' +
          '       <b class="price_origin" style="text-decoration: line-through;margin-left: 5px;">' + formatMoney(res.product.variants[0].price) + '</b>' +
          '     </p>' +
          '  </div>' +
          '  <div class="pull-right"><a class="btn text-danger btn-remove-product-buy-together" style="font-size: 16px;color: red;"><i class="fa fa-trash"></i></a></div>\n' +
          '</div>'
        );
        modal.modal('hide');
      }
    });
  }

});

$(document).on('click', '.product_buy_together', function (e) {
  e.stopPropagation();
  var row = $(this).closest('.row');
  var id = row.data('id');
  var product_buy_together_id = row.data('product_buy_together_id');
  var price_sale = row.data('price_sale');
  var promotion = row.data('promotion');
  var status = row.data('status');
  var modal = $('#modal-edit-product-buy-together');
  modal.find('select[name="product_buy_together"]').val(product_buy_together_id);
  modal.find('input[name="price_sale"]').val(price_sale);
  modal.find('input[name="promotion"]').val(promotion);
  modal.find('.btn-create-product-buy-together').attr('data-id', id);
  modal.modal('show');
});

//variant
var numAttribute = 0;
var attribute_used = [];

$(document).on('change', '.select-attribute', function () {
  var chosen = $(this).closest('.item-attribute').find('.chosen-select');
  var id = $(this).val();
  if (id == -1) {
    chosen.html('').prop('disabled', true).trigger("chosen:updated");
  } else {
    $.get('/admin/api/getChildAttribute?parent_id=' + id, function (json) {
      if (!json.code) {
        var option = '';
        json.data.forEach(function (element, index) {
          option += tmpl('attribute-option', element.name);
        })
        chosen.html(option);
        chosen.prop('disabled', false).trigger("chosen:updated");
      }
    });
  }

  disabledOptionSelectAttribute();

});

$(document).on('change', '.list-options .chosen-select', loadVariant);

$(document).on('click','.select-variant a', function () {
  var self = $(this)
  // $('.variant-item').find('input[type="checkbox"]').prop('checked', false)
  if (self.hasClass('select-all')){
    $('input[name="check-variant"]').prop('checked', true)
  }  else{
    $('input[name="check-variant"]').prop('checked', false)
  }
  // }
  // else{
  //   var val = self.text();
  //   var result = $('.table-variant').find('td:contains("' + val + '")');
  //   result.each(function (index, element) {
  //     var tr = $(this).closest('tr');
  //     tr.find('.checkboxes').prop('checked', true)
  //   })
  // }
  if ($('.table-variant').find('.checkboxes').length == $('.table-variant').find('.checkboxes:checked').length){
    $('.table-variant').find('.select-all').prop('checked', true);
  }
})

$(document).on('click', '.btn-change-variant', function () {
  var modal = $(this).closest('.modal');
  var data = {};
  var variantId = [];
  $('.table-variant tbody :checked').each(function () {
    variantId.push($(this).val());
  })
  data.price = modal.find('input[name="price"]').val();
  data.price = strToPrice(data.price);
  data.price_compare = modal.find('input[name="price_compare"]').val();
  data.price_compare = strToPrice(data.price_compare);
  data.stock_quant = modal.find('input[name="inventory"]').val();
  data.variantId = variantId;
  $.ajax({
    type: 'PUT',
    url: '/admin/variant',
    data: data,
    success: function (json) {
      if (!json.code){
        reloadPage();
        toastr.success('Cập nhật thành công')
      } else toastr.error('Có lỗi xảy ra, vui lòng thử lại!');
    }
  })
})

$(document).on('click','.select-variant a',function () {
  if ($('.table-variant tbody input:checkbox:checked').length){
    $('.action-box').removeClass('hidden');
    var numberOfChecked = $('.table-variant tbody input:checkbox:checked').length;
    $('.num-select').html(' ' + numberOfChecked + ' ');
  } else $('.action-box').addClass('hidden');
})

function disableInventory() {
  var check = $('input[name="stock_manage"]').is(':checked');
  if (check) {
    $(document).find('input[name="variant-inventory"]').prop('disabled', false);
  } else {
    $(document).find('input[name="variant-inventory"]').prop('disabled', true);
  }
}

function createItemVariant(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/variant',
    data: {
      data
    },
    success: function(json) {
      if(!json.code) {
        toastr.success('Thêm phiên bản thành công');
        reloadPage();
      } else toastr.error(json.message);
    }
  });
}

function updateItemVariant(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/variant/' + id,
    data: data,
    success: function(json) {
      if(!json.code) {
        toastr.success('Cập nhật thành công')
        reloadPage();
      } else toastr.error(json.message);
    }
  });
}

$(document).on('click','.modal-create-edit-variant span.remove',function () {
  var self = $(this);
  self.closest('.image').remove();
});

$(document).on('click', '.btn-remove-product-buy-together', function () {
  var row = $(this).closest('.row')
  var name = row.find('.product_buy_together').text();
  var id = $(this).data('id');
  popupConfirm('Xóa sản phẩm '+ name +' khỏi danh sách mua kèm?', function (result) {
    if (result) {
      if (!id) row.remove();
      else {
        $.ajax({
          type: 'DELETE',
          url: '/admin/product_buy_together/' + id,
          success: function (json) {
            if(!json.code) toastr.success("Xóa thành công");
            row.remove();
          }
        });
      }
    }
  });
});

function getDataAttribute() {
  var value = {};
  $('.list-options .chosen-select').each(function (index, element) {
    var id = $(this).closest('.item-option').data('id');
    if ($(this).val().length) {
      value['option_' + id] = $(this).val();
      console.log("option", $(this).val());
    }
  })
  return value;
}

function combinations(variants) {
    return (function recurse(keys) {
        if (!keys.length) return [{}];
        let result = recurse(keys.slice(1));
        return variants[keys[0]].reduce( (acc, value) =>
            acc.concat( result.map( item =>
                Object.assign({}, item, { [keys[0]]: value })
            ) ),
            []
        );
    })(Object.keys(variants));
}

function loadVariant() {

  var dataAttribute = getDataAttribute();
  // console.log("test", dataAttribute);
  // var N = 1;
  // for (var option of Object.keys(dataAttribute)) {
  //   var L = dataAttribute[option].length;
  //   if (L) {
  //     N *= L;
  //   }
  // }
  // console.log("test", N);
  //
  // var result = [];
  // for (var i = 0; i < N; i++) {
  //   result[i] = {};
  // }
  //
  // for (var option of Object.keys(dataAttribute)) {
  //   var options = dataAttribute[option];
  //   console.log("option in", options);
  //   var L = options.length;
  //   if (L == 0) continue;
  //   for (var i = 0; i < L; i++) {
  //     var index = i;
  //     while (index < N) {
  //       result[index][option] = options[i];
  //       index += L;
  //     }
  //   }
  // }
  var result = combinations(dataAttribute);

  console.log("test result", combinations(dataAttribute));

  var list_variant = '';
  var product_price = $('input[name="product_price"]').val();
  var product_price_compare = $('input[name="product_price_compare"]').val();

  if (result.length) {
    $('.box-product-price').addClass('hidden');
  } else {
    $('.box-product-price').removeClass('hidden');
  }
  console.log(result);

  if (result[0]) {
    var listVariant = result.map(item => {
      var arr = [];
      for (let index of Object.keys(item)) {
        arr.push(item[index]);
      }
      item.title = arr.join('/');
      return item;
    });
    listVariant.forEach(function (element, index) {
      list_variant += tmpl('add-variant', {
        option: element,
        price: product_price,
        price_compare: product_price_compare
      });
    });
  }
  $('.list-all-variant').html(list_variant);
}

$('.modal-create-edit-variant').on('show.modal.bs', function() {
  $('.modal-create-edit-variant').find('.list-attributes .form-group').each(function() {
    var self = $(this);
    var id = self.data('id');
    if (id) {
      $.get('/admin/api/getChildAttribute?parent_id=' + id, function(res) {
        if (!res.code && res.data) {
          var select = '<option value="" disabled selected>Chọn...</option>';
          res.data.forEach(function(item) {
            select += '<option value="'+item.name+'">'+item.name+'</option>';
          });
          self.find('select.input-option').html(select);
          if (self.find('select.input-option').attr('data-value')) {
            self.find('select.input-option').val(self.find('select.input-option').attr('data-value'));
          }
        }
      });
    }
  });

  var check_stock_manage = $('input[name="stock_manage"]').is(':checked');
  if (check_stock_manage) {
    $('.modal-create-edit-variant').find('input[name="variant_inventory"]').prop('disabled', false);
  } else {
    $('.modal-create-edit-variant').find('input[name="variant_inventory"]').val(1);
    $('.modal-create-edit-variant').find('input[name="variant_inventory"]').prop('disabled', true);
  }

});

$('#modal-product-buy-together').on('hidden.bs.modal', function() {
  $(this).find('select[name="product_buy_together"], input').val('');
});

$(document).on('keyup','.input-percent',function() {
  var input = $('input[name="promotion"]').val();
  if(input<0 || input>100)
  {
    $('input[name="promotion"]').val(100);
    toastr.error(' Giá trị nằm trong khoảng 0% đến 100%');
  }
  return;
});

// Automatically Format User Input
$(document).on('keyup','.formatMoney',function(){
  strToPrice($(this).val());
  var selection = window.getSelection().toString();
			if ( selection !== '' ) {
				return;
			}
			// When the arrow keys are pressed, abort.
			if ( $.inArray( event.keyCode, [38,40,37,39] ) !== -1 ) {
				return;
			}
			var $this = $( this );

			// Get the value.
			var input = $this.val();

			var input = input.replace(/[\D\s\._\-]+/g, "");
          input = input.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
					$this.val( function() {
						return input;
					} );
});

// Sanitize the values.
function strToPrice(price){
  if (price)  {
    price = price.replace(/,/g, '');
    return parseInt(price);
  }
  return price;
}

//numver to String format money
function numToStringPrice(num){
  if(num) return  num.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
}

$(document).ready(function(){
  checkVariantcreate();
  $(document).on('click','.btn-duplicate-product',function(){
    var product_id = $(this).data('id');
    $(this).prop('disabled', true);
    var data = {};
    data.title = $(this).closest('.modal-dialog').find('input[name="name-duplicate-product"]').val();
    var handle = createHandle(data.title);
    if (!data.title) {
      toastr.error('Tên sản phẩm mới không được rỗng.');
      $(this).prop('disabled', false);
      return;
    }
    $.ajax({
        type: 'POST',
        url: '/admin/product/duplicate/' + product_id,
        data: data,
        success: function (json) {
          if (json.id) {
            toastr.success("Nhân bản thành công");
          }
          reloadPage('/admin/product/' + json.id);
        }
      })
  })
})

$(document).on('click', '.list-image .active-feauture-image', function(event) {
  event.preventDefault();
  var self = $(this);
  var name = self.closest('.image').data('name');
  if (self.hasClass('active')) {
    self.removeClass('active');
  } else{
    $('.list-image .active-feauture-image').removeClass('active');
    self.addClass('active');
  }
});

// publish

$('.timepicker').timepicker({
  showSeconds: true,
  showMeridian: false,
  showInputs: false,
  minuteStep: 5
});


$(document).on('change', 'select[name="stop_selling"]', function () {
  console.log("test", $(this).val());
  if ($(this).val() == 'publish' || $(this).val() == 'stopSelling' ) {
    $('.publish-date').addClass('hidden');
  }
  else {
    $('.publish-date').removeClass('hidden');
  }
});
