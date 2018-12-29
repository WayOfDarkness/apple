
// initDataTable('table');
var modelName = 'product';
var hostname = location.protocol + '//' + location.host;
var table1 = $('#table-restore').DataTable( {
    dom: 'Bfrtip',
    responsive: true,
    order: [],
    columnDefs: [{
      targets: 0,
      orderable: false,
      order: []
    }, ],
    ajax: {
        url: hostname + '/admin/api/restore/product',
        dataSrc: 'data'
    },
    columns: [
        { data: 'button'},
        { data: 'id' },
        { data: 'title' },
        { data: 'updated_at' },
        { data: 'type'}
    ],
    buttons: [
            {
                text: 'Sản phẩm',
                action: function ( e, dt, node, config ) {
                    getDataRestore('product', node );
                },
                className: 'btn btn-object active'
            },
            {
                text: 'Bài viết',
                action: function ( e, dt, node, config ) {
                    getDataRestore('article', node );
                },
                className: 'btn btn-object'
            },
            {
                text: 'Nhóm sản phẩm',
                action: function ( e, dt, node, config ) {
                      getDataRestore('collection', node );
                },
                className: 'btn btn-object'
            },
            {
                text: 'Nhóm bài viết',
                action: function ( e, dt, node, config ) {
                      getDataRestore('blog', node );
                },
                className: 'btn btn-object'

            },
            {
                text: 'Trang nội dung',
                action: function ( e, dt, node, config ) {
                      getDataRestore('page', node );
                },
                className: 'btn btn-object'

            },
            {
                text: 'Galley',
                action: function ( e, dt, node, config ) {
                      getDataRestore('gallery', node );
                },
                className: 'btn btn-object'
            }
        ]
} );

function getDataRestore(type, node){
  table1.ajax.url( '/admin/api/restore/'+ type ).load();
  $('.btn-object').removeClass('active');
  node.addClass('active');
  $('.action-box').addClass('hidden');
  modelName = type;
  $('#title-restore').text(getText(type));
}

function getText(type){
  var data = '';
  switch (type) {
    case 'article':
       data = 'bài viết ';
      break;
    case 'blog':
       data = 'nhóm bài viết ';
      break;
    case 'product':
       data = 'sản phẩm ';
      break;
    case 'collection':
       data = 'nhóm sản phẩm ';
      break;
    case 'gallery':
    data = 'gallery ';
      break;
    case 'client':
      data = 'khách hàng ';
      break;
    case 'testimonial':
      data = 'khách hàng nói về chúng tôi ';
      break;
    case 'page':
      data = 'trang ';
      break;
    case 'contact':
      data = 'liên hệ ';
      break;
    default:
      break;
  }
  return data;
}


$(document).on('click', '.status-restore', function () {
  var arrId = [];
  $('tbody input:checkbox:checked').each(function () {
    arrId.push($(this).val());
  })
  var numSelected = $('tbody input:checkbox:checked').length;
  popupConfirm('Bạn có muốn khôi phục ' + numSelected + ' mục đã chọn không?', function (result) {
    if (result) {
      $.ajax({
        type: 'POST',
        url: '/admin/api/restore',
        data: {
          type: modelName,
          arrId: arrId,
          status: 'active'
        },
        success: function (json) {
          if (!json.code) {
            $('tbody input:checkbox:checked').each(function () {
              $(this).closest('tr').remove();
            })
            $('.action-box').addClass('hidden');
            toastr.success('Khôi phục thành công!');
            if ($(window).width() < 1035) {
              location.reload();
            }
          }
          else {
              toastr.error('Có lỗi xảy ra vui lòng thử lại!');
          }
        }
      })
    }
  })
});
