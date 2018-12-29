$('.btn-create-update').click(function(event) {
  var self = $(this);
  var name = self.data('name');
  var email_title = $('#email-title').val();
  var data_name = $('#email-title').attr('name');
  var setting = {};
  setting[data_name] = email_title;
  var content = $('#content').val();
  $.ajax({
    url: '/admin/email-templates',
    type: 'PUT',
    data: JSON.stringify({
      name: name,
      setting: setting,
      content: content
    }),
    contentType: "application/json",
    dataType: 'json',
    success: function(json){
      if(!json.code){
        toastr.success('Cập nhập thành công');
        reloadPage();
      }
    }
  })
});

$(document).on('click', '.btn-preview', function(event) {
  var content = $('#content').val();
  var modal = $('#modal-preview');
  var boxShow = modal.find('.body-email');
  boxShow.html(content);
  modal.modal('show');
});
