initDataTable('table');
var modelName = 'role';

var data = {};
data['order'] = [{
  id: "order",
  text: "Đơn hàng",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/order'
      }
    },
    {
      text: "Tìm kiếm",
      data: {
        method: 'GET',
        endpoint: '/order/search'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/order/{id}'
      }
    },
    {
      text: "Mới",
      data: {
        method: 'GET',
        endpoint: '/order?order_status=new'
      }
    },
    {
      text: "Đã xác nhận",
      data: {
        method: 'GET',
        endpoint: '/order?order_status=confirm'
      }
    },
    {
      text: "Chưa thanh toán",
      data: {
        method: 'GET',
        endpoint: '/order?payment_status=0'
      }
    },
    {
      text: "Hoàn tất",
      data: {
        method: 'GET',
        endpoint: '/order?order_status=done'
      }
    },
    {
      text: "Bị hoàn trả",
      data: {
        method: 'GET',
        endpoint: '/order?order_status=return'
      }
    },
    {
      text: "Bị hủy",
      data: {
        method: 'GET',
        endpoint: '/order?order_status=cancel'
      }
    },
    {
      text: "Tạo",
      data: {
        method: 'GET',
        endpoint: '/order/create'
      }
    },
    {
      text: "Sửa",
      data: {
        method: 'PUT',
        endpoint: '/order/{id}'
      }
    },
    {
      text: "Tính phí giao hàng",
      data: {
        method: 'GET',
        endpoint: '/api/order/getpriceghtk/{id}'
      }
    },
    {
      text: "Đăng đơn hàng",
      data: {
        method: 'GET',
        endpoint: '/api/order/setpriceghtk/{id}'
      }
    },
    {
      text: "Hủy đơn hàng",
      data: {
        method: 'GET',
        endpoint: '/order/{id}'
      }
    }
  ]
}];
data['product'] = [{
  id: "product",
  text: "Sản phẩm",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/product'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/product/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/product/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/product/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/product'
      }
    },
    {
      text: "Nhân bản",
      data: {
        method: 'POST',
        endpoint: '/product/duplicate/{id}'
      }
    },
    {
      text: "Nhập sản phẩm",
      data: {
        method: 'GET',
        endpoint: '/import_product'
      }
    }
  ]
}];
data['collection'] = [{
  id: "collection",
  text: "Nhóm sản phẩm",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/collection'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/collection/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/collection/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/collection/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/collection'
      }
    }
  ]
}];
data['product_buy_together'] = [{
  id: "product_buy_together",
  text: "Sản phẩm mua kèm",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/product_buy_together'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/product_buy_together/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/product_buy_together/create'
      }
    },
    {
      text: "Thêm trong trang sản phẩm",
      data: {
        method: 'POST',
        endpoint: '/product_buy_together/one'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/product_buy_together/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/product_buy_together/{id}'
      }
    }
  ]
}];

data['attribute'] = [{
  id: "attribute",
  text: "Thuộc tính sản phẩm",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/attribute'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/attribute/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/attribute/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/attribute/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/attribute/{id}'
      }
    }
  ]
}];
data['coupon'] = [{
  id: "coupon",
  text: "Mã giảm giá",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/coupon'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/coupon/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/coupon/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/coupon/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/coupon'
      }
    }
  ]
}];
data['sale'] = [{
  id: "sale",
  text: "Chương trình giảm giá",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/sale'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/sale/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/sale/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/sale/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/sale'
      }
    }
  ]
}];
data['customer'] = [{
  id: "customer",
  text: "Khách hàng",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/customer'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/customer/{id}'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/customer/{id}'
      }
    },
    {
      text: "Export",
      data: {
        method: 'GET',
        endpoint: '/customer/export'
      }
    },
    {
      text: "Xem tất cả đơn hàng",
      data: {
        method: 'GET',
        endpoint: '/customer/order/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/customer'
      }
    }
  ]
}];
data['article'] = [{
  id: "article",
  text: "Bài viết",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/article'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/article/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/article/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/article/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/article'
      }
    },
    {
      text: "Nhân bản",
      data: {
        method: 'POST',
        endpoint: '/article/duplicate/{id}'
      }
    }
  ]
}];
data['blog'] = [{
  id: "blog",
  text: "Nhóm bài viết",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/blog'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/blog/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/blog/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/blog/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/blog'
      }
    }
  ]
}];
data['page'] = [{
  id: "page",
  text: "Trang nội dung",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/page'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/page/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/page/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/page/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/page'
      }
    }
  ]
}];
data['bank'] = [{
  id: "bank",
  text: "Ngân hàng",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/bank'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/bank/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/bank/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/bank/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/bank'
      }
    }
  ]
}];
data['gallery'] = [{
  id: "gallery",
  text: "Thư viện",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/gallery'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/gallery/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/gallery/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/gallery/{id}'
      }
    },
    {
      text: "Cập nhật độ ưu tiên",
      data: {
        method: 'PUT',
        endpoint: '/api/gallery/updatePriority'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/gallery'
      }
    }
  ]
}];
data['photo'] = [{
  id: "photo",
  text: "Hình ảnh",
  state: {
    opened: true
  },
  data: 'parent',
  children: [{
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/photo/create'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/photo/{id}'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/photo/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/photo/{id}'
      }
    }
  ]
}];
data['review'] = [{
  id: "review",
  text: "Đánh giá",
  state: {
    opened: true
  },
  data: 'parent',
  children: [{
    text: "Danh sách",
    data: {
      method: 'GET',
      endpoint: '/review'
    }
  },
  {
    text: "Xem chi tiết",
    data: {
      method: 'GET',
      endpoint: '/review/{id}'
    }
  },
  {
    text: "Chỉnh sửa",
    data: {
      method: 'PUT',
      endpoint: '/review/{id}'
    }
  }
]
}];
data['subscriber'] = [{
  id: "subscriber",
  text: "Đăng ký nhận tin",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/subscriber'
      }
    },
    {
      text: "Xóa",
      data: {
        method: 'DELETE',
        endpoint: '/subscriber'
      }
    }
  ]
}];
data['comment'] = [{
  id: "comment",
  text: "Bình luận",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Xem tất cả",
      data: {
        method: 'GET',
        endpoint: '/comment'
      }
    },
    {
      text: "Thuộc sản phẩm",
      data: {
        method: 'GET',
        endpoint: '/comment/product'
      }
    },
    {
      text: "Thuộc bài viết",
      data: {
        method: 'GET',
        endpoint: '/comment/article'
      }
    },
    {
      text: "Trả lời",
      data: {
        method: 'PUT',
        endpoint: '/comment/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/comment'
      }
    }
  ]
}];
data['contact'] = [{
  id: "contact",
  text: "Liên hệ",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Xem tất cả",
      data: {
        method: 'GET',
        endpoint: '/contact'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/contact/{id}'
      }
    },
    {
      text: "Mới",
      data: {
        method: 'GET',
        endpoint: '/contact?status=unread'
      }
    },
    {
      text: "Chưa phản hồi",
      data: {
        method: 'GET',
        endpoint: '/contact?status=unreply'
      }
    },
    {
      text: "Cập nhật trạng thái",
      data: {
        method: 'PUT',
        endpoint: '/contact/updateStatus'
      }
    },
    {
      text: "Xóa",
      data: {
        method: 'DELETE',
        endpoint: '/contact/{id}'
      }
    }
  ]
}];
data['menu'] = [{
  id: "menu",
  text: "Menu",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/menu'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/menu/create'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/menu/{id}'
      }
    },
    {
      text: "Cập nhật độ ưu tiên",
      data: {
        method: 'PUT',
        endpoint: '/menu/updatePriority'
      }
    },
    {
      text: "Sửa",
      data: {
        method: 'PUT',
        endpoint: '/menu/{id}'
      }
    },
    {
      text: "Xóa một menu",
      data: {
        method: 'DELETE',
        endpoint: '/menu/{id}'
      }
    },
    {
      text: "Xóa nhiều menu",
      data: {
        method: 'DELETE',
        endpoint: '/menu'
      }
    }
  ]
}];

data['setting'] = [{
  id: "setting",
  text: "Thiết lập chung",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Xem",
      data: {
        method: 'GET',
        endpoint: '/setting'
      }
    },
    {
      text: "Sửa",
      data: {
        method: 'PUT',
        endpoint: '/setting'
      }
    }
  ]
}];

data['library'] = [{
  id: "library",
  text: "Library",
  state: {
    opened: true
  },
  data: 'parent',
  children: [{
      text: "Xem",
      data: {
        method: 'GET',
        endpoint: '/library'
      }
    }
  ]
}];

data['shipping_fee'] = [{
  id: "shipping_fee",
  text: "Phí vận chuyển",
  state: {
    opened: true
  },
  data: 'parent',
  children: [{
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/shipping_fee'
      }
    },
    {
      text: "Chi tiết",
      data: {
        method: 'GET',
        endpoint: '/shipping_fee/edit/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/shipping_fee/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/shipping_fee/{id}'
      }
    },
    {
      text: "Xóa",
      data: {
        method: 'DELETE',
        endpoint: '/shipping_fee/{id}'
      }
    }
  ]
}];

data['testimonial'] = [{
  id: "testimonial",
  text: "Đối tác nói về chúng tôi",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/testimonial'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/testimonial/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/testimonial/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/testimonial/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/testimonial'
      }
    }
  ]
}];

data['shop'] = [{
  id: "shop",
  text: "Hệ thống cửa hàng",
  state: {
    opened: true
  },
  data: 'parent',
  children: [{
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/shop'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/shop/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/shop/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/shop/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/shop/{id}'
      }
    }
  ]
}];

data['client'] = [{
  id: "client",
  text: "Khách hàng tiêu biểu",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/client'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/client/{id}'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/client/create'
      }
    },
    {
      text: "Cập nhật",
      data: {
        method: 'PUT',
        endpoint: '/client/{id}'
      }
    },
    {
      text: "Xoá",
      data: {
        method: 'DELETE',
        endpoint: '/client'
      }
    }
  ]
}];

data['user'] = [{
  id: "user",
  text: "Quản trị viên",
  state: {
    opened: true
  },
  data: 'parent',
  children: [
    {
      text: "Danh sách",
      data: {
        method: 'GET',
        endpoint: '/user'
      }
    },
    {
      text: "Lịch sử hoạt động",
      data: {
        method: 'GET',
        endpoint: '/user/history'
      }
    },
    {
      text: "Thêm mới",
      data: {
        method: 'GET',
        endpoint: '/user/create'
      }
    },
    {
      text: "Xem chi tiết",
      data: {
        method: 'GET',
        endpoint: '/user/{id}'
      }
    },
    {
      text: "Sửa",
      data: {
        method: 'PUT',
        endpoint: '/user/{id}'
      }
    },
    {
      text: "Nhận email khi có đơn hàng",
      data: {
        method: 'GET',
        endpoint: '/user/email/order'
      }
    },
    {
      text: "Nhận email khi có liên hệ",
      data: {
        method: 'GET',
        endpoint: '/user/email/contact'
      }
    },
    {
      text: "Nhận email khi có subscribe",
      data: {
        method: 'GET',
        endpoint: '/user/email/subscribe'
      }
    },
    {
      text: "Xóa",
      data: {
        method: 'DELETE',
        endpoint: '/user/{id}'
      }
    }
  ]
}];

$('.box-tree').each(function() {
  var role_id = $('input[name="role_id"]').val();
  var item = $(this).data('item');
  var element = $(this).find('.tree');
  var _data = data[item];
  if (role_id) {
    getPermission(role_id, item, function(json) {
      if (json.count && json.data.length) {
        var children = _data[0]['children'];
        $.each(children, function(index, elem) {
          $.each(json.data, function(i, e) {
            var obj = {
              method: e.method,
              endpoint: e.endpoint
            };
            if (JSON.stringify(obj) == JSON.stringify(elem.data)) {
              _data[0]['children'][index].state = { checked: true};
            }
          });
        });
        initJsTree(element, _data);
      } else initJsTree(element, _data);
    });
  } else initJsTree(element, _data);
});

function initJsTree(element, data) {
  element.jstree({
    core: {
      data: data,
      check_callback: false,
      themes: {
        name: 'proton',
        responsive: true
      }
    },
    checkbox: {
      three_state : true,
      whole_node : false,
      tie_selection : false,
      keep_selected_style: false
    },
    plugins: ['checkbox']
  });
}

$('.btn-create-update-role').click(function() {
  var id = $(this).data('id');
  $('input').removeClass('error')
  var data = {};
  data.title = $('input[name="title"]').val();
  if(!data.title) {
    $('input[name="title"]').addClass('error');
    return toastr.error('Chưa nhập tên phân quyền');
  }
  data.permission = [];
  $('.box-tree').each(function() {
    var group = $(this).data('item');
    var tree = $(this).find('.tree');
    $.each(tree.jstree("get_checked", true), function() {
      if (this.data != 'parent' && this.data.method && this.data.endpoint) {
        var obj = {
          group: group,
          method: this.data.method,
          endpoint: this.data.endpoint
        };
        data.permission.push(obj);
        if (obj.method == 'GET' && obj.endpoint == '/' + obj.group + '/create') {
          data.permission.push({
            group: obj.group,
            method: 'POST',
            endpoint: '/' + obj.group
          });
        }
        if (obj.method == 'GET' && obj.endpoint == '/import_product') {
          data.permission.push({
            group: obj.group,
            method: 'GET',
            endpoint: '/import_product/template'
          });
          data.permission.push({
            group: obj.group,
            method: 'POST',
            endpoint: '/import_product'
          });
        }
      }
    });
  });

  $(this).addClass('disabled');
  if (id) updateRole(id, data);
  else createRole(data);
});

function createRole(data) {
  $.ajax({
    type: 'POST',
    url: '/admin/role',
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        toastr.success('Tạo thành công');
        reloadPage('/admin/role/' + json.id);
      } else if (json.code == -1) {
        toastr.error('Phân quyền đã tồn tại');
      } else if (json.code == -4) {
        toastr.error(json.message);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

function updateRole(id, data) {
  $.ajax({
    type: 'PUT',
    url: '/admin/role/' + id,
    data: data,
    success: function(json) {
      $(document).find('.disabled').removeClass('disabled');
      if(!json.code) {
        toastr.success('Cập nhật thành công');
      } else if (json.code == -1) {
        toastr.error('Phân quyền đã tồn tại');
      } else if (json.code == -4) {
        toastr.error(json.message);
      } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
}

$(document).on('click', '.btn-remove', function() {
  var id = $(this).data('id');
  var tr = $(this).closest('tr');
  popupConfirm("Xóa phân quyền?", function(result) {
    if (result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/role/' + id,
        success: function(json) {
          if(!json.code) {
            toastr.success('Xóa thành công');
            tbl.row(tr).remove().draw();
          } else if (json.code == -1) {
            toastr.error('Phân quyền này đang được sử dụng');
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      });
    }
  });
});

function getPermission(role_id, group, fn) {
  var url = '/admin/api/permission?role_id=' + role_id + '&group=' + group;
  $.get(url, fn);
}
