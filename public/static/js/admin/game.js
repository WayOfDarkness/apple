initDataTable('table');
var modelName = 'game';

$('.tinymce').each(function() {
  var id = $(this).attr('id');
  initTinymce('#' + id);
});

$('.btn-create-update').click(function () {
  $(document).find('.error').removeClass('error');
  var data = {};
  data.name = $('input[name="title"]').val();
  data.handle = $('input[name="handle"]').val();
  data.image = $('input[name="image"]').val();
  data.description = $('textarea[name="description"]').val();
  data.requirement = tinyMCE.get('requirement').getContent();
  data.status = $('select[name="status"]').val();
  data.priority = $('input[name="priority"]').val();
  data.parent_id = $('select[name="parent_id"]').val();

  if (!data.name.trim().length) {
    toastr.error('Chưa nhập tên game');
    return $('input[name="title"]').addClass('error');
  }

  if (!data.handle) {
    toastr.error('Chưa nhập đường dẫn');
    return $('input[name="handle"]').parent().addClass('error');
  }

  $(this).addClass('disabled');

  var id = $(this).data('id');
  if (id) updateGame(id, data);
  else createGame(data);
});

function createGame(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/game',
    data: data,
    success: function (json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        toastr.success('Tạo thành công');
        updateSEO('game', json.id);
        updateMetafield('game', json.id);
        reloadPage('/admin/game/' + json.id);
      } else if (json.code == -1) {
        toastr.error('Game đã tồn tại');
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateGame(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/game/' + id,
    data: data,
    success: function (json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        toastr.success('Cập nhật thành công');
        updateSEO('game', id);
        updateMetafield('game', id);
        reloadPage();
      } else if (json.code == -1) {
        toastr.error('Game đã tồn tại');
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

$(document).on('click', '.btn-remove', function () {
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  popupConfirm('Xóa Game', function (result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/game/' + id,
        success: function (json) {
          if (!json.code) {
            toastr.success('Xóa game thành công');
            tbl.row(tr).remove().draw();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      });
    }
  });
});

$(document).on('click','.btn-remove-article-icon',function () {
  var self = $(this);
  var li = self.closest('li');
  var article_id = self.data('id');
  var articleName = li.find('a').text();
  var game_id = $('.btn-create-update').data('id');
  popupConfirm('Bạn có muốn xóa bài viết ' + articleName + ' khỏi game không?',function (result) {
    if (result){
      $.ajax({
        type: 'DELETE',
        url: '/admin/api/game/removeArticle',
        data:{
          'article_id' : article_id,
          'game_id' : game_id
        },
        success: function (code) {
          if (!code){
            toastr.success('Xóa bài viết ' + articleName + ' khỏi nhóm thành công');
            li.remove();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      })
    }
  })
})

$(".space").sortable({
  connectWith: '.space',
  tolerance: 'intersect',
  update: function(event, ui) {
    updatePriority();
  }
});

function updatePriority() {
  var listArticle = [];
  var gameID = parseInt($('.listDragProduct').data('id'));
  $('.listDragProduct.ui-sortable').find('li').each(function(index, element) {
    var id = parseInt($(element).attr('data-id'));
    listArticle.unshift(id);
  });
  $.ajax({
    type: 'PUT',
    url: '/admin/api/gameArticle/updatePriority',
    data: {
      listArticle: listArticle,
      gameID: gameID
    },
    success: function(res) {
      console.log(res);
    }
  });
}

$(".space").disableSelection();
