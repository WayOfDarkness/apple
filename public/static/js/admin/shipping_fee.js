initDataTable('table');
var modelName = 'shipping_fee';

$(".chosen-select").chosen({width: "100%"});

$(document).on('change', 'select[name="type"]', function(event) {
  var self = $(this);
  var value = self.val();
  $('input[name="from"]').toggleClass('disabled');
  $('input[name="to"]').toggleClass('disabled');
});

$('.chosen-select').on('change', function () {
  var regionId = $(this).val();
  $('.btn-shipping-add').removeClass('hidden');
  $.ajax({
    type: 'GET',
    url: '/admin/api/subregion/' + regionId,
    success: function (html) {
      $('#modal-subregion').remove();
      $('.shipping-fee').append(html);
    }
  });
  loadShipingFee(regionId);
});


function loadShipingFee(regionid) {
  $.ajax({
    type: 'GET',
    url: '/admin/api/shipping_fee/fee/' + regionid,
    success: function (html) {
      $('.box-shipping-fee .box').each(function (index, value) {
        $(this).remove();
      })
      $('.box-shipping-fee').append(html);
    }
  })
}

$(document).on('click', '.btn-create-shipping', function () {
  var modal = $(this).closest('.modal');
  var idShippingFee = modal.data('id');
  var regionId = modal.data('regionid');
  var data = {};
  var dataSubregion = [];
  data.title = $('input[name="title"]').val();
  if (!data.title.trim().length) {
    toastr.error('Chưa nhập tên phương thức vận chuyển');
    $('input[name="title"]').addClass('error');
    return;
  }
  data.type = $('select[name="type"]').val();
  data.from = strToPrice($('input[name="from"]').val());
  data.to = strToPrice($('input[name="to"]').val());
  data.price = strToPrice($('input[name="price"]').val());
  data.region_id = regionId;

  $('input[name="subregion_id"]').each(function (index) {
    var element = {};
    element.subregion_id = $(this).data('id');
    element.price = strToPrice($(this).val());
    if (element.price != '')
      dataSubregion.push(element);
  })
  if (!idShippingFee) {
    $.ajax({
      type: 'POST',
      url: '/admin/shipping_fee',
      data: {
        data: data,
        subRegion: dataSubregion
      },
      success: function (json) {
        if (!json.code) {
          toastr.success('Tạo phương thức thành công');
          reloadPage('/admin/shipping_fee/edit/' + regionId);
        } else if (json.code == -1) toastr.error('Phương thức đã tồn tại');
        else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
      }
    });
  }
  else {
    $.ajax({
      type: 'PUT',
      url: '/admin/shipping_fee/' + idShippingFee,
      data: {
        data: data,
        subRegion: dataSubregion
      },
      success: function (json) {
        if (!json.code) {
          toastr.success('Sửa phương thức thành công');
          reloadPage('/admin/shipping_fee/edit/' + regionId);
        } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
      }
    });
  }

});

$(document).on('click', '.btn-edit-shipping-fee', function () {
  var id = $(this).data('id');
  $('.btn-create-shipping').html('Sửa');
  $("#modal-subregion").find(".modal-title").text("Sửa phương thức");
  $.ajax({
    type: 'GET',
    url: '/admin/api/shipping_fee/' + id,
    success: function (json) {
      var data = json.data;
      var subregion = json.subregion;
      $('#modal-subregion').attr('data-regionid', data.region_id);
      $('#modal-subregion').attr('data-id', data.id);
      data.title ? $('#modal-subregion input[name="title"]').val(data.title) : '';
      data.type ? $('#modal-subregion select[name="type"]').val(data.type) : '';
      data.from ? $('#modal-subregion input[name="from"]').val(_formatMoney(data.from)) : '';
      data.to ? $('#modal-subregion input[name="to"]').val(_formatMoney(data.to)) : '';
      data.price ? $('#modal-subregion input[name="price"]').val(_formatMoney(data.price)) : '';
      if(data.type == 'all'){
        $('input[name="from"]').addClass('disabled');
        $('input[name="to"]').addClass('disabled');
      } else{
        $('input[name="from"]').removeClass('disabled');
        $('input[name="to"]').removeClass('disabled');
      }
      $('#modal-subregion input[name="subregion_id"]').val('');
      subregion.forEach(function (value, index) {
        $('#modal-subregion input[name="subregion_id"]').each(function () {
          if ($(this).data('id') == value.subregion_id) {
            $(this).val(_formatMoney(value.price))
          }
        })
      })
    }
  });
})

$(document).on('click', '.btn-shipping-add', function () {
  $('.btn-create-shipping').html('Thêm');
  $("#modal-subregion").find(".modal-title").text("Thêm phương thức");
  $('.modal').removeAttr('data-id');
  $('.modal :input').each(function () {
    if ($(this).attr('name') != 'type')
      $(this).val('');
  })
})

$(document).on('click', '.btn-remove-shipping', function () {
  var id = $(this).data('id');
  var box = $(this).closest('.box-shipping-fee .box');
  popupConfirm('Xóa phương thức vận chuyển', function (result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/shipping_fee/' + id,
        success: function (json) {
          if (!json.code) {
            toastr.success('Xóa phương thức vận chuyển thành công');
            box.remove().draw();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      });
    }
  })
})
