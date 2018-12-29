$(document).ready(function(){
  var orderId = $('.btn-update-order').data('id');

  var statusShipping = $('.shipping-status').data('status');

  //kiểm tra nút thêm đơn hàng

  if (statusShipping == 0) {
    $.get('/admin/api/order/getpriceghtk/' + orderId, function(json){
      if (!json.code) {
        $("#shipping-charge").html(json.result.fee.fee + ' VND');
      }
      else {
          $("#shipping-charge").html(json.message);
          $('.set-order-shipping').addClass('hidden');
      }
    })
    $('.new-shipping').removeClass('hidden');
    $('.ordered-shipping').addClass('hidden');
  }

  $('.set-order-shipping').click(function(){
    $.get('/admin/api/order/setpriceghtk/' + orderId, function(json){
      if (json.success) {
        toastr.success(" Gửi đơn hàng thành công");
        location.reload();
      }
      else {
        toastr.error(json.message);
      }
    })
  })
  $('.cancel-order-shipping').click(function(){
    $.get('/admin/api/order/cancelghtk/' + orderId, function(json){
      if (json.success) {
        toastr.success(" Hủy đơn hàng thành công");
        location.reload();
      }
      else {
        toastr.error(json.message);
      }
    })
  })
})
