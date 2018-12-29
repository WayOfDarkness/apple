initDataTable('table');
var modelName = 'blog';

$('.tinymce').each(function() {
  var id = $(this).attr('id');
  initTinymce('#' + id);
});

$('.btn-create-update').click(function () {
  $(document).find('.error').removeClass('error');
  var data = {};
  data.title = $('input[name="title"]').val();
  data.handle = $('input[name="handle"]').val();
  data.image = $('input[name="image"]').val();
  data.description = $('textarea[name="description"]').val();
  data.content = tinyMCE.get('content').getContent();
  data.status = $('select[name="status"]').val();
  data.priority = $('input[name="priority"]').val();
  data.parent_id = $('select[name="parent_id"]').val();
  data.tags = $("input[name='tags']").tagsinput('items');
  data.template = $('select[name="template"]').val() || '';

  if (!data.title.trim().length) {
    toastr.error('Chưa nhập tiêu đề');
    return $('input[name="title"]').addClass('error');
  }

  if (!data.handle) {
    toastr.error('Chưa nhập đường dẫn');
    return $('input[name="handle"]').parent().addClass('error');
  }

  data.multiLang = [];
  if (languages) {
    $.each(languages, function(index, elem) {
      var obj = {};
      obj.lang = elem;
      obj.id = $('input[name="translattion_'+ elem +'"]').val()
      obj.title = $('input[name="title_'+ elem +'"]').val();
      obj.handle = $('input[name="handle_' + elem + '"]').val() || data.handle;
      obj.description = $('textarea[name="description_' + elem + '"]').val();
      obj.content = tinyMCE.get("content_" + elem).getContent();
      data.multiLang.push(obj);
    });
  }

  $(this).addClass('disabled');

  var id = $(this).data('id');
  if (id) updateBlog(id, data);
  else createBlog(data);
});

function createBlog(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/blog',
    data: data,
    success: function (json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        toastr.success('Tạo thành công');
        updateSEO('blog', json.id);
        updateMetafield('blog', json.id);
        reloadPage('/admin/blog/' + json.id);
      } else if (json.code == -1) {
        toastr.error('Nhóm bài viết đã tồn tại');
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateBlog(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/blog/' + id,
    data: data,
    success: function (json) {
      $(document).find('.disabled').removeClass('disabled');
      if (!json.code) {
        toastr.success('Cập nhật thành công');
        updateSEO('blog', id);
        updateMetafield('blog', id);
        reloadPage();
      } else if (json.code == -1) {
        toastr.error('Nhóm bài viết đã tồn tại');
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

$(document).on('click', '.btn-remove', function () {
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  popupConfirm('Xóa nhóm bài viết', function (result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/blog/' + id,
        success: function (json) {
          if (!json.code) {
            toastr.success('Xóa blog thành công');
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
  var blog_id = $('.btn-create-update').data('id');
  popupConfirm('Bạn có muốn xóa bài viết ' + articleName + ' khỏi nhóm bài viết không?',function (result) {
    if (result){
      $.ajax({
        type: 'DELETE',
        url: '/admin/api/blog/removeArticle',
        data:{
          'article_id' : article_id,
          'blog_id' : blog_id
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
  var blogID = parseInt($('.listDragProduct').data('id'));
  $('.listDragProduct.ui-sortable').find('li').each(function(index, element) {
    var id = parseInt($(element).attr('data-id'));
    listArticle.unshift(id);
  });
  $.ajax({
    type: 'PUT',
    url: '/admin/api/blogArticle/updatePriority',
    data: {
      listArticle: listArticle,
      blogID: blogID
    },
    success: function(res) {
      console.log(res);
    }
  });
}

$(".space").disableSelection();
