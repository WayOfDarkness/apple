initTinymce('#description');
initDataTable('table');
var modelName = 'coupon';

$('select[name="type"]').on('change', function () {
  var self = $(this);
  var value = self.val();
  $('input[name="value"]').val('');
  if (value == 'percent') {
    $('.max-value-percent').removeClass('hidden');
    $('.value-coupon').removeClass('hidden');
    $('input[name="value"]').addClass('limit');
    $('input[name="value"]').removeClass('formatMoney');
  } else if (value == 'freeship') {
    $('.max-value-percent').addClass('hidden');
    $('.value-coupon').addClass('hidden');
  } else {
    $('.value-coupon').removeClass('hidden');
    $('.max-value-percent').addClass('hidden');
    $('input[name="value"]').removeClass('limit');
    $('input[name="value"]').addClass('formatMoney');
  }
});

$(document).on('keyup', 'input[name="value"]', function(){
  var self = $(this);
  if(self.hasClass('limit')){
    handleChange(self);
  }
});

$('.btn-create-update').click(function () {
  $(document).find('.error').removeClass('error');
  var data = {};
  data.title = $('input[name="title"]').val();
  data.code = $('input[name="code"]').val();
  data.type = $('select[name="type"]').val();
  data.value = strToPrice($('input[name="value"]').val());
  data.min_value_order = strToPrice($('input[name="min_value_order"]').val());
  data.usage_left = $('input[name="usage_left"]').val();
  data.description = tinyMCE.get('description').getContent();
  data.start_date = $('input[name="start_date"]').val();
  data.end_date = $('input[name="end_date"]').val();
  data.status = $('select[name="status"]').val();
  if (!data.title) {
    $('input[name="title"]').addClass('error');
    return toastr.error('Chưa nhập tiêu đề');
  }
  if (data.min_value_order < 0) {
    $('input[name="min_value_order"]').addClass('error');
    return toastr.error('Giá trị đơn hàng tối thiểu không được nhỏ hơn 0');
  }
  if (!data.start_date) {
    $('input[name="start_date"]').addClass('error');
    return toastr.error('Chưa nhập ngày bắt đầu');
  }
  if (!data.end_date) {
    $('input[name="end_date"]').addClass('error');
    return toastr.error('Chưa nhập ngày kết thúc');
  }
  if (data.type == 'percent') {
    if (!$('input[name="max_value_percent"]').val()) {
      $('input[name="max_value_percent"]').addClass('error');
      return toastr.error('Chưa nhập giá trị tối đa');
    } else {
      data.max_value_percent = strToPrice($('input[name="max_value_percent"]').val());
    }
  }
  data.start_date = dmy2ymd(data.start_date);
  data.end_date = dmy2ymd(data.end_date);
  if (new Date(data.end_date) < new Date(data.start_date)) {
    $('input[name="end_date"]').addClass('error');
    return toastr.error('Ngày kết thúc không được bé hơn ngày bắt đầu');
  }
  if (!data.code) {
    $('input[name="code"]').addClass('error');
    return toastr.error('Chưa nhập mã giảm giá');
  }
  if (!data.usage_left) {
    $('input[name="usage_left"]').addClass('error');
    return toastr.error('Chưa nhập số lượng mã giảm giá');;
  }
  if (!data.value && data.type != 'freeship') {
    $('input[name="value"]').addClass('error');
    return toastr.error('Chưa nhập giá trị');;
  }
  if (data.type == 'freeship') {
    data.value = 0;
  }

  $(this).addClass('disabled');
  var id = $(this).data('id');
  if (id) updateCoupon(id, data);
  else createCoupon(data);
});

function createCoupon(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/coupon',
    data: data,
    success: function (json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        toastr.success('Tạo mã giảm giá thành công');
        reloadPage('/admin/coupon/' + json.id);
      } else if (json.code == -1) toastr.error('Coupon đã tồn tại');
      else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateCoupon(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/coupon/' + id,
    data: data,
    success: function (json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        toastr.success('Cập nhật thành công');
        reloadPage();
      } else if (json.code == -1) {
        toastr.error('Coupon đã tồn tại')
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

$(document).on('click', '.btn-remove-coupon', function () {
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  popupConfirm('Xóa mã giảm giá', function (result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/coupon/' + id,
        success: function (json) {
          if (!json.code) {
            toastr.success('Xóa mã giảm giá thành công');
            tbl.row(tr).remove().draw();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      });
    }
  })
})

function handleChange(input) {
  if (input.val() < 0) input.val(0);
  if (input.val() > 100) input.val(100);
}
