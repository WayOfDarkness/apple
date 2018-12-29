// $('.table-order-customer').dataTable({
//   bPaginate: false,
//   bFilter: false,
//   bLengthChange: false,
//   info: false,
//   ordering: false
// });
// $('.table-list-product').dataTable({
//   bPaginate: false,
//   bFilter: false,
//   bLengthChange: false,
//   info: false,
//   ordering: false,
//   language: {
//       emptyTable: "Chưa có sản phẩm nào"
//     }
// });
$(document).ready(function() {
    var search_params = location.search;
    initDataTableAjax('table', '/admin/api/getOrderPaginate'+ search_params, '/admin/api/exportOrderExcel'+ search_params);
});

$('.btn-update-order').click(function() {
  var id = $(this).data('id');
  var self = $(this);
  var data = {};
  data.order_status = $('select[name="order_status"]').val();
  data.payment_status = $('select[name="payment_status"]').val();
  data.shipping_status = $('select[name="shipping_status"]').val();
  data.reason_cancel = $('textarea[name="reason_cancel"]').val();
  self.addClass('disabled');
  $.ajax({
    type: 'PUT',
    url: '/admin/order/' + id,
    data: data,
    success: function(json) {
      self.removeClass('disabled');
      if(!json.code) {
        toastr.success('Cập nhật thành công');
        reloadPage();
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
});

$('select[name="order_status"]').change(function() {
  var status = $(this).val();
  $('.reason_cancel').addClass('hidden');
  if ((status == 'cancel') || (status == 'return')) {
    $('.reason_cancel').removeClass('hidden');
  }
});

$(document).on('click','.status-order',function () {
  var arrId = [];
  $('tbody input:checkbox:checked').each(function () {
    arrId.push($(this).val());
  })
  var status = $(this).data('value');
  var numSelected = $('tbody input:checkbox:checked').length;
  popupConfirm('Bạn có muốn cập nhật ' + numSelected + ' mục đã chọn không?', function (result) {
    if (result) {
      $.ajax({
        type: "POST",
        url: '/admin/api/order/updateStatusOrder',
        data: {
          'arrId': arrId,
          'status': status
        },
        success: function (json) {
          toastr.success('Thành công');
          reloadPage();
        }
      });
    }
  });
})

$(document).on('click','.status-payment',function () {
  var arrId = [];
  $('tbody input:checkbox:checked').each(function () {
    arrId.push($(this).val());
  })
  var status = $(this).data('value');
  var shipping_status = $(this).data('shipping');
  $.ajax({
    type: "POST",
    url: '/admin/api/order/updateStatusPayment',
    data: {
      'arrId': arrId,
      'status': status,
      'shipping_status': shipping_status
    },
    success: function (json) {
      toastr.success('Thành công');
      reloadPage();
    }
  })
})
$( "select[name='payment_status'],select[name='shipping_status']" ).change(function(){
  if($("select[name='payment_status']").val()== "1" && $("select[name='shipping_status']").val()== "2"){
    $("select[name='order_status']").val("done").attr('disabled', 'disabled');
  }
  else if($("select[name='payment_status']").val()== "1" && ($("select[name='shipping_status']").val()== "0" || $("select[name='shipping_status']").val()== "1")){
    $("select[name='order_status']").val("confirm").attr('disabled', 'disabled');
  }
  else {
    $("select[name='order_status']").removeAttr('disabled');
  }
});

$('#modal-edit-customer .btn-update').click(function() {
  var id = $(this).data('id');
  var type = $(this).data('type');
  $('input').removeClass('error');
  var data = {};
  data.name = $('input[name="name"]').val();
  data.phone = $('input[name="phone"]').val();
  data.email = $('input[name="email"]').val();
  data.address = $('input[name="address"]').val();
  data.region = $('select[name="region"]').val();
  data.subregion = $('select[name="subregion"]').val();
  data.orderFee = $(document).find('.order_fee').attr('data-value') - 0;

  if(!data.name) {
    toastr.error('Chưa nhập họ tên');
    $('input[name="name"]').addClass('error');
    return;
  }
  if(!data.email) {
    toastr.error('Chưa nhập email');
    $('input[name="email"]').addClass('error');
    return;
  }

  if (!isEmail(data.email)) {
    toastr.error('Chưa đúng định dạng email');
    $('input[name="email"]').addClass('error');
    return;
  }

  if (data.phone && !isPhone(data.phone)) {
    toastr.error('Số điện thoại chưa đúng định dạng');
    $('input[name="phone"]').addClass('error');
    return;
  }

  $(this).addClass('disabled');
  if (type == 'customer') updateCustomer(id, data);
  else updateShipping(id, data);
});

function updateCustomer(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/customer/' + id,
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        $('#modal-edit-customer').modal('hide');
        toastr.success('Cập nhật thành công');
        reloadPage();
      } else if (json.code == -1) {
        toastr.error('Khách hàng đã tồn tại');
      } else if (json.code == -4) {
        toastr.error(json.message);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateShipping(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/api/updateShipping/' + id,
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        $('#modal-edit-customer').modal('hide');
        toastr.success('Cập nhật thành công');
        reloadPage();
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

$('.btn-toggle-chart').click(function() {
  if ($(this).hasClass('active')) {
    $(this).removeClass('active');
    $('.box-order-chart').slideDown(500);
  } else {
    $(this).addClass('active');
    $('.box-order-chart').slideUp(500);
  }
});


$('.icon-remove-coupon').click(function() {
  popupConfirm('Bạn có muốn xóa mã giảm giá cho sản phẩm không?', function (result) {
    if (result){
      var order_id = $('input[name="order_id"]').val();
      $.ajax({
        type: 'PUT',
        url: '/admin/api/order/'+order_id+'/discount',
        success: function(res) {
          if (!res.code) {
            toastr.success("Xóa mả giảm giá thành công");
            reloadPage();
          } else toastr.error(res.message);
        }
      });
    }
  })
})
// nhập số cho giảm giá đơn hàng
$(document).on('keypress','.bootbox-input-number', function() {
  inputPositiveNumbers( event );
});

$('.btn-edit-order-discount').click(function() {
  var value = $(this).data('value');
  popup();
  function popup() {
    popupPrompt("Nhập giảm giá đơn hàng", "number", function (result) {
      if (result != null && result < 0) {
        toastr.error("Giảm giá tối thiểu bằng 0");
        popup();
      } else if(result != null && result > value){
        toastr.error("Giảm giá tối đa bằng hoặc nhỏ hơn giá trị đơn hàng");
        popup();
      } else {
        if (result) {
          var order_id = $('input[name="order_id"]').val();
          $.ajax({
            type: 'PUT',
            url: '/admin/api/order/'+order_id+'/discount',
            data: {
              discount: result
            },
            success: function(res) {
              if (!res.code) {
                toastr.success("Cập nhật thành công");
                reloadPage();
              } else toastr.error(res.message);
            }
          });
        }
      }
    });
  }
});

$('.btn-edit-shipping-fee').click(function () {
  var value = $(this).data('value');
  popup();
  function popup() {
    popupPrompt("Nhập phí vận chuyển", "number", function (result) {
      if (result != null && result < 0) {
        toastr.error("Phí vận chuyển tối thiểu bằng 0");
        popup();
      } else {
        var order_id = $('input[name="order_id"]').val();
        $.ajax({
          type: 'PUT',
          url: '/admin/api/order/' + order_id + '/shipping_fee',
          data: {
            shipping_fee: result
          },
          success: function (res) {
            if (!res.code) {
              toastr.success("Cập nhật thành công");
              reloadPage();
            } else toastr.error(res.message);
          }
        });
      }
    });
  }
});

//check coupon
$('#modal-add-coupon form').submit(function(e) {
  e.preventDefault();
  checkCoupon();
  return false;
});

function checkCoupon(){
  var modal = $('#modal-add-coupon');
  modal.find('.btn-update').prop('disabled', true);
  modal.find('.text-discount').addClass('hidden');
  var coupon = modal.find('input[name="coupon"]').val();
  var subTotal = modal.find('input[name="subTotal"]').val();
  $.ajax({
    type: 'POST',
    url: '/api/checkCoupon',
    data: {
      coupon: coupon,
      subtotal: subTotal
    },
    success: function(res) {
      if (!res.code) {
        modal.find('.btn-update').prop('disabled', false);
        modal.find('.alert-success .coupon-discount').html(formatMoney(res.discount));
        modal.find('.alert-success').removeClass('hidden');
      } else {
        modal.find('.alert-error').html(res.message);
        modal.find('.alert-error').removeClass('hidden');
      }
    }
  });
}

$('#modal-add-coupon').on('hidden.bs.modal', function() {
  $(this).find('input[name="coupon"]').val('');
  $(this).find('.text-discount').addClass('hidden');
  $(this).find('.btn-update').prop('disabled', true);
});

$('#modal-add-coupon .btn-update').click(function() {
  var coupon = $('#modal-add-coupon').find('input[name="coupon"]').val();
  var id = $(this).data('id');
  $.ajax({
    type: 'PUT',
    url: '/admin/api/order/' + id + '/addCoupon',
    data: {
      code: coupon
    },
    success: function(res) {
      if (!res.code) {
        $('#modal-add-coupon').modal('hide');
        toastr.success("Thêm mã giảm giá thành công");
        reloadPage();
      } else {
        toastr.error(res.message);
      }
    }
  });
});


$(".datepicker-order").datepicker({
  dateFormat: 'dd-mm-yy',
  defaultDate: "+1w",
  maxDate: 'd',
  changeMonth: true,
  onSelect: function (date) {
    $(".endDatePicker").datepicker("option", "minDate", date);
  }
});

$('.select-region-order').on('change', function () {
  var val = $(this).val();
  var self = $(this);
  var box = self.closest('.box');
  var objFirst = {
    id: 0,
    name: 'Mời chọn quận/huyện...'
  };
  var htmlSub = tmpl('subregion-option', objFirst);
  $.get('/admin/api/order/loadSubregion?id=' + val, function (json) {
    if (!json.code){
      var data = json.data;
      data.forEach(function (element, index) {
        var obj = {
          id: element.id,
          name: element.name
        };
        htmlSub += tmpl('subregion-option', obj);
      });
      box.find('.select-subregion').html(htmlSub);
    }
  })
});

$('.search-product').on('focusin',function () {
  var val = $(this).val();
  loadProduct(val);
});

$('.search-product').keyup(function () {
  var val = $(this).val();
  loadProduct(val);
})

$(document).on('change','.quantity', function () {
  var val = $(this).val();
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  if (val == 0) tr.remove();
  var price = $(this).data('value');
  StoreAPI.changeItem(id, (Number(val) - 1 + 1), function () {});
  tr.find('.total').text(formatMoney(val*price));
  StoreAPI.getCart(function (json) {
    if (!json.code){
      var cart = json.cart;
      $('.price-craft').text(formatMoney(cart.total));
      $('.price-craft').attr('data-value',val*price);
      $('.price-total').text(formatMoney(cart.total));
    }
  });
  getShipping();
});

$(document).on('click','.btn-remove-product-icon', function () {
  var self = $(this);
  popupConfirm('Bạn muốn xóa sản phẩm khỏi giỏ hàng không?', function(result){
    if (result) {
      var tr = self.closest('tr');
      var val = 0;
      var id = self.data('id');
      var price = self.data('value');
      tr.remove();
      StoreAPI.removeItem(id, function () {});
      StoreAPI.getCart(function (json) {
        if (!json.code){
          var cart = json.cart;
          $('.price-craft').text(formatMoney(cart.total));
          $('.price-craft').attr('data-value',val*price);
          $('.price-total').text(formatMoney(cart.total));
          toastr.success('Xóa sản phẩm khỏi giỏ hàng thành công');
        }
      });
      getShipping();
    }
  });
})

$(document).on('click', '.item-variant', function () {
  var val = $(this).data('id');
  StoreAPI.addItem(val, 1, function () {});
  $.get('/admin/api/order/addProduct?id=' + val, function (json) {
    if (!json.code) {
      var check = false;
      var product = json.product;
      var variant = json.variant;
      $(document).find('.tr-list-product').each(function () {
        var self = $(this);
        var id = self.data('value');
        if (id == variant.id) {
          check = true;
          var quantity = self.find('.quantity').val();
          var price = self.find('.quantity').data('value');
          self.find('.quantity').val(Number(quantity) + 1);
          self.find('.total').text(formatMoney((Number(quantity) + 1) * Number(price)));
        }
      });
      if (!check) {
        var obj = {
          image: product.image ? '/uploads/' + resizeImage(product.image, 480) : '/static/img/no-image.png',
          product_id: product.id,
          product_title: product.title,
          variant_id: variant.id,
          variant_title: variant.title,
          price: variant.price,
          quantity: 1
        };
        $('.dataTables_empty').remove();
        var html = tmpl('add-order-product', obj);
        $('.table tbody').append(html);
      }
      $('.search-order .panel').addClass('hidden');
      StoreAPI.getCart(function (json) {
        if (!json.code){
          var cart = json.cart;
          // $('.input-shipping-fee').val(0);
          $('.price-craft').text(formatMoney(cart.total));
          $('.price-craft').attr('data-value', cart.total);
          $('.price-total').text(formatMoney(cart.total));
        }
      });
      if($('.shipping-fee').val() != 0){
          getShipping();
      }
    }
  } )
});

$('.btn-create-order').click(function () {
  var self = $(this);
  var data = {};
  var shipping_address = {};

  if (!$('.table-list-product tbody tr.tr-list-product').length){
    toastr.error('Vui lòng chọn ít nhất một sản phẩm!');
    return;
  }

  data.name = $('.content').find('input[name="name"]').val();
  data.phone = $('.content').find('input[name="phone"]').val();
  data.email = $('.content').find('input[name="email"]').val();
  data.address = $('.content').find('input[name="address"]').val();
  data.region = $('.content').find('select[name="order_region"]').val();
  data.subregion = $('.content').find('select[name="subregion"]').val();
  data.notes = $('.content').find('textarea[name="notes"]').val();

  if(!data.name.trim().length) {
    toastr.error('Chưa nhập họ tên');
    $('.content').find('input[name="name"]').addClass('error');
    return;
  }

  if(!data.phone) {
    toastr.error('Chưa nhập số điện thoại');
    $('.content').find('input[name="phone"]').addClass('error');
    return;
  }

  if(!data.email) {
    toastr.error('Chưa nhập địa chỉ email');
    $('.content').find('input[name="email"]').addClass('error');
    return;
  }

  if(!data.address) {
    toastr.error('Chưa nhập địa chỉ giao hàng');
    $('.content').find('input[name="address"]').addClass('error');
    return;
  }

  if(!Number(data.region)) {
    toastr.error('Chưa chọn tỉnh/thành phố giao hàng');
    $('.content').find('select[name="order_region"]').addClass('error');
    return;
  }

  if(!Number(data.subregion)) {
    toastr.error('Chưa chọn quận/huyện giao hàng');
    $('.content').find('select[name="subregion"]').addClass('error');
    return;
  }

  if (!isEmail(data.email)) {
    toastr.error('Chưa đúng định dạng email');
    $('.content').find('select[name="email"]').addClass('error');
    return;
  }

  if (!isPhone(data.phone)) {
    toastr.error('Số điện thoại chưa đúng định dạng');
    $('.content').find('select[name="phone"]').addClass('error');
    return;
  }

  data.payment_method = 'COD';
  data.shipping_price = $('.content').find('.order_fee').attr('data-value') - 0;

  shipping_address.name = $('.content').find('input[name="name_shipping"]').val();
  shipping_address.phone = $('.content').find('input[name="phone_shipping"]').val();
  shipping_address.email = $('.content').find('input[name="email_shipping"]').val();
  data.shipping_address = shipping_address;

  self.addClass('disabled');
  StoreAPI.checkout(data, function (json) {
    if (!json.code){
      toastr.success('Đặt hàng thành công!');
      reloadPage('/admin/order/' + json.order_id)
    }
    else{
      toastr.error('Có lỗi xảy ra!');
      self.removeClass('disabled');
    }
  })
});

function loadProduct(key = '') {
  var url = '/admin/api/order/loadProduct';
  if (key)  url += '?q=' + key;
  $.get(url, function (json) {
    if (!json.code){
      var data = json.data;
      var html = '';
      if (!data.length){
        html = 'Không tìm thấy sản phẩm nào';
      }
      else {
        data.forEach(function (element, index) {
          var variants = element.variants;
          var listVariant = '';
          variants.forEach(function (e, i) {
            var o = {
              id: e.id,
              title: e.title,
              price: formatMoney(e.price)
            };
            listVariant += tmpl('list-variant', o);
          });
          var obj = {
            title: element.title,
            image: element.image ? '/uploads/' + resizeImage(element.image, 480) : '/static/img/no-image.png',
            listVariant: listVariant
          };
          html += tmpl('list-product', obj);
        })
      }
      $('.search-order .panel').removeClass('hidden');
      $('.order-search-result ul').html(html);
    }
  })
}

$('html').click(function(event) {
  if ($(event.target).parents('.search-order').length==0) {
    $(this).find('.panel').addClass('hidden');
  }
});

$('.btn-edit-information').on('click', function () {
  var id = $(this).data('id');
  var type = $(this).data('type');
  var modal = $('#modal-edit-customer');
  var nameType = type=='customer'?' khách hàng':' giao hàng';
  modal.find('.name-type').text(nameType);
  $.get('/admin/api/order/loadInfo?type=' + type + '&id=' + id, function (json) {
    if (!json.code){
      var data = json.data;
      modal.find('input[name="name"]').val(data.name);
      modal.find('input[name="email"]').val(data.email);
      modal.find('input[name="phone"]').val(data.phone);
      modal.find('input[name="address"]').val(data.address);
      modal.find('select[name="region"]').val(data.region);
      var html = '';
      $.get('/admin/api/order/loadSubregion?id=' + data.region, function (json) {
        if (!json.code){
          var dataSubregion = json.data;
          dataSubregion.forEach(function (element, index) {
            var obj = {
              id: element.id,
              name: element.name
            };
            html += tmpl('subregion-option', obj);
          });
          modal.find('.select-subregion').html(html);
          modal.find('select[name="subregion"]').val(data.subregion);
          modal.find('.btn-update').attr('data-type', type);
          modal.find('.btn-update').attr('data-id', id);
          modal.find('.shipping').addClass('hidden');
          modal.modal('show');
        }
      });
    }
  });
});

$(document).on('change', '.select-subregion', function () {
  getShipping();
});

$(document).on('change', '.shipping-fee', function () {
  updateShippingFee();
});

$('.input-shipping-fee').keyup(function () {
  updateShippingFee();
});

function getShipping() {
  var regionId = $('.select-region-order').val();
  var subregionId = $('.select-subregion').val();
  var total = $(document).find('.price-craft').attr('data-value');
  var currentShip = '';
  if($('.shipping-fee').val() != 0){
    currentShip = $('.shipping-fee option:selected').html();
  }
  $.get('/api/getShipping?region_id=' + regionId + '&subregion_id=' + subregionId + '&total=' + total, function (json) {
    if (!json.code){
      var data = json.data;
      var html = '<option value=0>Tùy chỉnh phí vận chuyển</option>';
      data.forEach(function (element, index) {
        var obj = {
          price: element.price,
          name: element.title
        };
        if(currentShip == element.title){
          html += tmpl('shipping-option-checked', obj);
        }
        else {
          html += tmpl('shipping-option', obj);
        }
        // var valueOption =0;
        // if( currentShip == element.title){
        //   valueOption = element.price;
        // }
      });
      $('.shipping-fee').html(html);
      updateShippingFee();
    }
  })
}

function updateShippingFee() {
  var val = $('.shipping-fee').val();
  if (val != 0){
    $('.input-shipping-fee').addClass('hidden');
    $()
  } else{
    $('.input-shipping-fee').removeClass('hidden');
    val = strToPrice($('.input-shipping-fee').val());
  }
  var priceCraft = $('.price-craft').attr('data-value');
  $('.order_fee').text(formatMoney(val));
  $('.order_fee').attr('data-value', val);
  $('.price-total').text(formatMoney(Number(val) + Number(priceCraft)));
}
$(document).on('change','input[name="email"]',function(){
  var email = $(this).val();
  if (!isEmail(email)) {
    toastr.error('Chưa đúng định dạng email');
    $('input[name="email"]').addClass('error');
    return;
  }
})
$(document).on('change','input[name="phone"]',function(){
  var phone = $(this).val();
  if (!isPhone(phone)) {
    toastr.error('Chưa đúng định dạng phone');
    $('input[name="phone"]').addClass('error');
    return;
  }
})
$(document).on('change','input[name="getInfoCustomer"]',function(){
  checkAddInfo();
});
$(document).on('change','.info-user-order input, .info-user-order select',function(){
  checkAddInfo();
});

function checkAddInfo(){
  if ($('input[name="getInfoCustomer"]').is(":checked"))
  {
    var name = $('.content').find('input[name="name"]').val();
    var phone = $('.content').find('input[name="phone"]').val();
    var email = $('.content').find('input[name="email"]').val();
    var address = $('.content').find('input[name="address"]').val();
    var region = $('.content').find('select[name="order_region"]').val();
    var subregion = $('.content').find('select[name="subregion"]').val();
    var htmlSubRegion = $('.content').find('select[name="subregion"]').html();
    $('input[name="name_shipping"]').val(name).attr('disabled','disabled');
    $('input[name="phone_shipping"]').val(phone).attr('disabled','disabled');
    $('input[name="email_shipping"]').val(email).attr('disabled','disabled');
    $('input[name="address_shipping"]').val(address).attr('disabled','disabled');
    $('select[name="order_region_shipping"]').val(region).attr('disabled','disabled');
    $('select[name="subregion_shipping"]').html(htmlSubRegion);
    $('select[name="subregion_shipping"]').val(subregion).attr('disabled','disabled');
  }
  else {
    $('input[name="name_shipping"]').removeAttr('disabled');
    $('input[name="phone_shipping"]').removeAttr('disabled');
    $('input[name="email_shipping"]').removeAttr('disabled');
    $('input[name="address_shipping"]').removeAttr('disabled');
    $('select[name="order_region_shipping"]').removeAttr('disabled');
    $('select[name="subregion_shipping"]').removeAttr('disabled');
  }
}

$('.count').each(function () {
  $(this).text(numeral($(this).data('value')).format('0.0a'));
  $(this).prop('Counter', 0).animate({
    Counter: numeral($(this).data('value')).format('0.0a')
  }, {
    duration: 3000,
    easing: 'swing',
    step: function (now) {
      var last = numeral($(this).data('value')).format('0.0a').slice(-1);
      if ($.isNumeric(last)){
        $(this).text(Math.ceil(now));
      }
      else{
        $(this).text(Math.round(now*10)/10 + last.toUpperCase());
      }
    }
  });
})

$(document).on('change', '#modal-edit-customer .select-subregion', function () {
  var modal = $('#modal-edit-customer');
  var type = modal.find('.btn-update').attr('data-type');
  if (type == 'shipping'){
    modal.find('.shipping').removeClass('hidden');
  } else{
    modal.find('.shipping').addClass('hidden');
  }
});

// suggest tag coupon

$('.list-tags').on('click', '.tag-name', function() {
  var text = $(this).html();
  $("input[name='coupon']").val(text);
  checkCoupon();
});

//suggest coupon

$(document).ready(function() {
  function getLinkType(group) {
    $.get('/admin/api/order/loadCoupon', function(res) {
      if (!res.code) {
        var ul = '';
        $.each(res.data, function(index, element) {
          ul += '<li data-id="'+element.code+'">'+element.code+'</li>';
        });
        ul = ul + `<li data-id="element.code"><a href='/admin/coupon/create' target='_blank'>Thêm....</a></li>`

        group.find('.suggest-coupon ul.dropdown-menu').html(ul);
        group.find('.suggest-coupon ul.dropdown-menu').removeClass('hidden');
      }
    });
  }
  $(document).on('focus click', 'input[name="coupon"]',function(){
    var group = $(this).closest('.suggest-coupon').parent();
    var search = $(this).val();
    if (!search) {
      getLinkType(group);
    }
  })

  $('.dropdown-menu-search').on('click', 'li:not(:last-child)', function() {
    var group = $(this).closest('.suggest-coupon').parent();
    var text = $(this).text();
    group.find('.suggest-coupon input[name="coupon"]').val(text);
    checkCoupon();
  });

  var searchRequest = null;
  $('.suggest-coupon').on('keyup', 'input[name="coupon"]', function() {
    checkCoupon();
    var search = $(this).val();
    var group = $(this).closest('.suggest-coupon').parent();
      if (searchRequest != null) searchRequest.abort();
      searchRequest = $.get('/admin/api/order/loadCoupon?search=' + search, function(res) {
        if (!res.code) {
          if (res.data && res.data.length) {
            var ul = '';
            $.each(res.data, function(index, element) {
              ul += '<li data-id="'+element.code+'">'+element.code+'</li>';
            });
            ul = ul + `<li data-id="element.code"><a href='/admin/coupon/create' target='_blank'>Thêm....</a></li>`
            group.find('.suggest-coupon ul.dropdown-menu').html(ul);
            group.find('.suggest-coupon ul.dropdown-menu').removeClass('hidden');
          } else {
            var ul = `<li>Không có kết quả phù hợp</li>`;
            ul = ul + `<li data-id="element.code"><a href='/admin/coupon/create' target='_blank'>Thêm....</a></li>`

            group.find('.suggest-coupon ul.dropdown-menu').html(ul);
            group.find('.suggest-coupon ul.dropdown-menu').removeClass('hidden');
          }
        }
      });
  });
});
