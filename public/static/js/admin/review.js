initDataTable('table');
var modelName = 'review';

$(document).on('click', '.edit-review', function() {
  var id = $(this).data('id');
  $.get('/admin/review/' + id, function(res) {
    var modal = $('#modal-update-review');
    modal.find('.title').html(res.data.title);
    modal.find('.review-content').html(res.data.content);
    modal.find('.like').val(res.data.like);
    modal.find('.dislike').val(res.data.dislike);
    modal.find('.rating').val(res.data.rating);
    modal.find('.status').val(res.data.status);
    modal.find('form').attr('data-id', id);
    modal.modal('show');
  });
});

$('#modal-update-review').on('submit', 'form', function(e) {
  e.preventDefault();
  var id = $(this).attr('data-id');
  var data = $(this).serialize();
  $.ajax({
    type: 'PUT',
    url: '/admin/review/' + id,
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
});