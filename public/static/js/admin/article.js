// initDataTable('table');
var modelName = 'article';

$( function() {
    var value = $('#slider').data('value') ? $('#slider').data('value') : 0;
    var handle = $( "#custom-handle" );
    $( "#slider" ).slider({
      value: value,
      min: 0,
      max: 10,
      create: function() {
        handle.text( $( this ).slider( "value" ) );
      },
      slide: function( event, ui ) {
        handle.text( ui.value );
      }
    });
  } );

$(document).ready(function() {
    initDataTableAjax('table', '/admin/api/getArticlePaginate?type='+type_article + '&template='+template, '/admin/api/exportArticleExcel');
});

$('.tinymce').each(function () {
  var id = $(this).attr('id');
  initTinymce('#' + id);
});

$('.timepicker').timepicker({
  showInputs: false,
  minuteStep: 5
});

$(".chosen-select").chosen({width: "100%"});

$('.btn-create-update').click(function (event) {
  $(document).find('.error').removeClass('error');
  var data = {};
  data.type = type_article ? type_article : 'news';
  var update_type = $('select[name="type"]').val();
  if (update_type != type_article) {
    data.type =  update_type;
  }
  console.log(update_type);


  data.title = $('input[name="title"]').val();
  data.handle = $('input[name="handle"]').val();
  data.description = $('textarea[name="description"]').val();
  data.content = tinyMCE.get('content').getContent();
  data.game_id = $('.chosen-game[name="game"]').val();
  data.blogs = $('.chosen-blog[name="blog"]').val();
  data.image = $('input[name="image"]').val();
  data.status = $('select[name="status"]').val();
  data.priority = parseInt($('input[name=priority]').val());
  data.admin_point = typeof $("#slider").slider("value") === "number" ? $("#slider").slider("value") : 0;
  data.publish_date = $('input[name=publish_date]').val();
  data.publish_date = dmy2ymd(data.publish_date);
  data.publish_time = $('input[name="publish_time"]').val();
  data.tags = $("input[name='tags']").tagsinput('items');
  // data.author = $("input[name='author']").val();
  data.template = $('select[name="template"]').val() || '';

  data.listImage = [];
  $('.article-images').find('.list-image .image').each(function () {
    data.listImage.push($(this).attr('data-name'));
  });

  data.arrOption = [];

  $('.list-attributes .item-attribute').each(function (index, elem) {
    var option_value = $(this).find('select').val();
    if (option_value) {
      data.arrOption[index] = option_value;
    }
  });

  if (!data.title.trim().length) {
    toastr.error('Chưa nhập tiêu đề');
    $('input[name="title"]').addClass('error');
    return;
  }
  if (!data.handle) {
    toastr.error('Chưa nhập đường dẫn');
    $('input[name="handle"]').parent().addClass('error');
    return;
  }

  data.multiLang = [];

  $(this).addClass('disabled');
  console.log(data);
  // return;

  var id = $(this).data('id');
  if (id) updateArticle(id, data);
  else createArticle(data);
});

function createArticle(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/article',
    data: data,
    success: function (json) {
      if (!json.code) {
        toastr.success('Tạo thành công');
        updateSEO('article', json.id);
        updateMetafield('article', json.id);
        if (type_article == "review")
          reloadPage('/admin/article/' + json.id + "?template=analysis");
        else
          reloadPage('/admin/article/' + json.id);
      } else if (json.code == -1) {
        toastr.error('Bài viết đã tồn tại');
        $(document).find('.disabled').removeClass('disabled');
      } else {
        toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        $(document).find('.disabled').removeClass('disabled');
      }
    }
  });
}

function updateArticle(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/article/' + id,
    data: data,
    success: function (json) {
      if (!json.code) {
        toastr.success('Cập nhật thành công');
        updateSEO('article', id);
        updateMetafield('article', id);
        reloadPage();
      } else if (json.code == -1) {
        toastr.error('Bài viết đã tồn tại');
        $(document).find('.disabled').removeClass('disabled');
      } else {
        toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        $(document).find('.disabled').removeClass('disabled');
      }
    }
  });
}

$(document).on('click', '.btn-remove', function () {
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  popupConfirm('Xóa bài viết', function (result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/article/' + id,
        success: function (json) {
          if (!json.code) {
            toastr.success('Xóa bài viết thành công');
            tbl.row(tr).remove().draw();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      });
    }
  })
})

$(document).on('change', 'select[name="status"]', function () {
  if ($(this).val() == 'notPublish') {
    $('.publish-date').removeClass('hidden');
  }
  else {
    $('.publish-date').addClass('hidden');
  }
});


$(document).on('click', '.btn-remove-blog', function () {
  var arrIdBlog = [];
  $('#modal-remove-blog :checked').each(function () {
    arrIdBlog.push($(this).val());
  })
  var arrId = [];
  $('tbody input:checkbox:checked').each(function () {
    arrId.push($(this).val());
  })
  $.ajax({
    type: 'DELETE',
    url: '/admin/api/blog/deleteMultipleBlog',
    data: {
      'arrIdBlog': arrIdBlog,
      'arrId': arrId
    },
    success: function (json) {
      $('#modal-remove-blog').modal('hide');
      toastr.success('Xóa khỏi nhóm bài viết thành công');
    }
  })
})

$(document).on('click', '.btn-add-blog', function () {
  var arrIdblog = [];
  $('#modal-add-blog :checked').each(function () {
    arrIdblog.push($(this).val());
  })
  var arrId = [];
  $('tbody input:checkbox:checked').each(function () {
    arrId.push($(this).val());
  })
  $.ajax({
    type: 'POST',
    url: '/admin/api/blog/addMultipleBlog',
    data: {
      'arrIdBlog': arrIdblog,
      'arrId': arrId
    },
    success: function (json) {
      $('#modal-add-blog').modal('hide');
      toastr.success('Thêm vào nhóm bài viết thành công');
    }
  })
})

var author = $('input[name="author"]');
author.autocomplete({
 source: function( request, response ){
   $.ajax({
      url: "/admin/api/author?input=" + request.term,
      async:false,
      success: function( data ) {
        var array = data.id.map(function(item){
          return item.name;
        })
        response( array);
      }
    });
 }
});

$(document).ready(function(){
  $(document).on('click','.btn-duplicate-artile',function(){
    var article_id = $(this).data('id');
    $(this).prop('disabled', true);
    var data = {};
    data.title = $(this).closest('.modal-dialog').find('input[name="name-duplicate-artile"]').val();
    var handle = createHandle(data.name);
    if (!data.title) {
      toastr.error('Tên sản phẩm mới không được rỗng.');
      $(this).prop('disabled', false);
      return;
    }
    $.ajax({
        type: 'POST',
        url: '/admin/article/duplicate/' + article_id,
        data: data,
        success: function (json) {
          if(json.id){
            toastr.success("Nhân bản thành công");
          }
          reloadPage('/admin/article/' + json.id);
        }
      })
  })
})
