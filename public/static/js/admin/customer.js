initDataTable('.customer-table');
var modelName = 'customer';

$('.btn-update').click(function() {
  var id = $(this).data('id');
  $('input').removeClass('error');
  var data = {};
  data.name = $('input[name="name"]').val();
  data.phone = $('input[name="phone"]').val();
  data.email = $('input[name="email"]').val();
  data.address = $('input[name="address"]').val();
  data.company = $('input[name="company"]').val();
  data.member_type = $('select[name="member-type"]').val();
  data.region = $('select[name="region"]').val();
  data.subregion = $('select[name="subregion"]').val();

  if(!data.name) {
    toastr.error('Chưa nhập họ tên');
    return $('input[name="name"]').addClass('error');
  }
  $(this).addClass('disabled');
  updateCustomer(id, data);
});


function updateCustomer(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/customer/' + id,
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        toastr.success('Cập nhật thành công');
        reloadPage();
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}
