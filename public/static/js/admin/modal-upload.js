var elementImage;
var chooseMultipleImage = false;
var uploadType = '';
var active = 'home';

let designBoldWidth = 300;
let designBoldHeight = 300;

function showModalUpload() {
  $('#modal-upload').modal('show');
  $('#modal-upload .db-btn-designit')
    .attr('data-href',
      `https://www.designbold.com/design-it/create/custom/${designBoldWidth}/${designBoldHeight}/px?app_id=5d46ebb068`);
}
$("#modal-upload").on("click", ".tab-folder", function() {
  $(".name").text($(this).data('name'));
})
$("#modal-upload").on("click", ".tab-home", function() {
  $(".name").text("Home");
})
$(document).on('click', '.item-choose-image img', function() {
  elementImage = $(this).parent();
  if (!ENABLE_NEW_EXPLORER) {
    designBoldWidth = elementImage.data('width') || 300;
    designBoldHeight = elementImage.data('height') || 300;
    showModalUpload();
  } else {
    EXPLORER.setMode({
      multiple: false
    });
    EXPLORER.open();
  }
});

$(document).on('click', '.db-btn-designit', function() {
  const modal = $("#modal-upload").data("bs.modal");
  const modalIsShown = modal ? modal.isShown : false;
  if (!modalIsShown) {
    elementImage = $($(this).siblings('.item-choose-image')[0]);
  }
});

$("#modal-upload").on("click", ".tab-folder", function() {
  $(".modal-body .tab-folder.active").removeClass("active");
  $(this).addClass('active');
  active = $(this).data('name');
  $('.modal-body .tab-pane.active').removeClass('active in');
  $('#' + active).addClass('active in');
});

$(document).on('click', '.choose-image-variant .add-image', function() {
  elementImage = $(this).parent();
  if (!ENABLE_NEW_EXPLORER) {
   chooseMultipleImage = true;
   uploadType = 'variant';
   $('#modal-upload').modal('show');
 } else {
   EXPLORER.setMode({
     multiple: true
   });
   EXPLORER.setCustomAttr({
     uploadType: 'variant'
   });
   EXPLORER.open();
 }
});

$(document).on('click', '.choose-image-product .add-image', function() {
  elementImage = $(this).parent();
  if (!ENABLE_NEW_EXPLORER) {
    chooseMultipleImage = true;
    uploadType = 'product';
    $('#modal-upload').modal('show');
  } else {
    EXPLORER.setMode({
      multiple: true
    });
    EXPLORER.setCustomAttr({
      uploadType: 'product'
    });
    EXPLORER.open();
  }
});

$(document).on('click', '.choose-image-article .add-image', function() {
  elementImage = $(this).parent();
  if (!ENABLE_NEW_EXPLORER) {
    chooseMultipleImage = true;
    uploadType = 'product';
    $('#modal-upload').modal('show');
  } else {
    EXPLORER.setMode({
      multiple: true
    });
    EXPLORER.setCustomAttr({
      uploadType: 'product'
    });
    EXPLORER.open();
  }
});

$(document).on('click', '.item:not(form)', function() {
  if (!chooseMultipleImage) $('.file-manager').find('.item.active').removeClass('active');
  if ($(this).hasClass('active')) {
    $(this).removeClass('active');
  } else {
    $(this).addClass('active');
  }
  var countActive = $('.file-manager').find('.item.active').length;
  if (countActive) $('.modal-footer').find('.btn-choose-image').removeClass('disabled');
  else $('.modal-footer').find('.btn-choose-image').addClass('disabled');
});

$('.btn-choose-image').click(function() {
  addImage();
});
$(document).on('dblclick','#modal-upload .list-image .item',function() {
    $(this).addClass('active');
    addImage();
});
function addImage(){
  if (!$("#modal-upload").hasClass("send-src-to-tinymce")) {
    if (!chooseMultipleImage) {
      var name = $('.file-manager').find('.item.active').attr('data-src');
      if (name == undefined) {
        toastr.options = {
            "preventDuplicates": true,
            "preventOpenDuplicates": true
          };
        toastr.error("Chọn 1 hình");
        return;
      }
      var src = '/uploads/' + resizeImage(name, 480);
      elementImage.addClass('active');
      elementImage.find('img').attr('src', src);
      elementImage.find('.value').val(name);
      elementImage.find('.value').trigger('change');
      $('#modal-upload').modal('hide');
    } else {
      if (uploadType == 'variant') {
        $('.file-manager').find('.item.active').each(function(index, elem) {
          var name = $(this).attr('data-src');
          var obj = {
            name: name,
            src: '/uploads/' + resizeImage(name, 480)
          };
          var item_image = tmpl("add-item-image", obj);
          elementImage.find('.add-image').before(item_image);
        });
        $('#modal-upload').modal('hide');
      } else if (uploadType == 'product') {
        $('.file-manager').find('.item.active').each(function(index, elem) {
          var name = $(this).attr('data-src');
          var obj = {
            name: name,
            src: '/uploads/' + resizeImage(name, 480)
          };
          var item_image = tmpl("add-item-image-product", obj);
          elementImage.find('.add-image').before(item_image);
        });
        $('#modal-upload').modal('hide');
      } else if (uploadType == 'gallery-photo') {
        $('.file-manager').find('.item.active').each(function (index, elem) {
          var name = $(this).attr('data-src');
          var obj = {
            name: name,
            src: '/uploads/' + resizeImage(name, 480)
          };
          var item_image = tmpl("item-photo-gallery", obj);
          elementImage.find('.list-photos').append(item_image);
          checkItem();
        });
        $('#modal-upload').modal('hide');
      }
    }
  } else {
    var name = $('.file-manager').find('.item.active').attr('data-src');
    if (name == undefined) {
      toastr.options = {
        "preventDuplicates": true,
        "preventOpenDuplicates": true
      };
      toastr.error("Chọn 1 hình");
      return;
    }
    $(".mce-reset").first().find('.mce-textbox').first().val("/uploads/" + name);
    $('#modal-upload').modal('hide');
    $('#modal-upload').removeClass("send-src-to-tinymce");
  }
  $(".modal-body").find(".item.active").removeClass("active");
}


function readURL(files, callback) {
  function loadOne(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var imgData = reader.result;
      output.push(imgData);
      if (output.length == files.length) {
        callback(output);
      }
    };
    reader.readAsDataURL(file);
  }
  var output = [];
  for (var i = 0; i < files.length; i++) {
    loadOne(files[i]);
  }
}

function uploadImageURL(url) {
  $.ajax({
    type: 'POST',
    url:'/admin/api/downloadImage',
    data: { url },
    cache: false,
    success: json => {
      if (!json.code) {
        const {fileName} = json;
        const obj = {src: `${location.origin}/uploads/${fileName}`, image: fileName};

        const modal = $("#modal-upload").data("bs.modal");
        const modalIsShown = modal ? modal.isShown : false;
        if (modalIsShown) {
          const self = $('#upload-image-tab-home');
          self.closest('.add-image').after(tmpl('item-upload-image', obj));
        }
        else {
          const src = '/uploads/' + resizeImage(fileName, 480);
          elementImage.addClass('active');
          elementImage.find('img').attr('src', src);
          elementImage.find('.value').val(fileName);
        }
        toastr.success('Thêm thành công 1 hình ảnh từ DesignBold');
      }
      else {
        toastr.error(json.message);
      }
    }
  });
}

$('.modal-body').on('change', '.upload-image', function() {
  var self = $(this);
  if($(this).val()) {
    var form = $(this).closest('form');
    var active = form.closest('.tab-pane').data('name');
    form.addClass('disabled');
    var formData = new FormData(form[0]);
    for (var value of formData.values()) {
      if (!checkExtImage(value.name)) {
        form.removeClass('disabled');
        return;
      }
    }
    $.ajax({
      type: 'POST',
      url: '/admin/api/uploadImage/' + active,
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success: function(json) {
        if (!json.code) {
          var list = '';
          $.each(json.data, function(i, e) {
            var src = location.origin + '/uploads/' + e;
            var obj = {
              'src': src,
              'image': e
            }
            list += tmpl('item-upload-image', obj)
          });
          self.closest('.add-image').after(list);
          form.removeClass('disabled');
          toastr.success('Thêm thành công ' + json.total + " hình ảnh");
        }
        else{
          form.removeClass('disabled');
          toastr.error(json.message);
        }
      }
    });
  }
  $(this).val("");
});

$(document).on('click', '.item-choose-image .remove-image', function() {
  var parent = $(this).parent();
  var default_img = parent.data('default');
  parent.find('img').attr('src', default_img);
  parent.find('.value').val('');
  parent.removeClass('active');
});

$('#modal-upload').on('hidden.bs.modal', function() {
  uploadType = '';
  chooseMultipleImage = false;
});

$(document).on('click', '.btn-create-folder', function () {
  var modal = $(this).closest('.modal');
  var name = modal.find('input[name="name_folder"]').val();
  if (name == "") {
    toastr.error('Tên thư mục rỗng');
    $(".btn-create-folder").addClass("disabled");
    modal.find('input[name="name_folder"]').on("keyup", function() {
      $(".btn-create-folder").removeClass("disabled");
    })
    return;
  }
  $.ajax({
    type: 'POST',
    url: '/admin/api/createFolder',
    data: {
      name: name
    },
    success: function (json) {
      if (!json.code){
        toastr.success('Tạo thư mục thành công');
        modal.modal('hide');
        var obj = {
          name: json.name
        }
        var htmlFolder = tmpl('item-tab-folder', obj);
        var htmlTab = tmpl('item-tab-image', obj);
        $('#modal-upload .modal-sidebar').find('ul').append(htmlFolder);
        $('#modal-upload .modal-container').find('.modal-body').append(htmlTab);
        // sortFolder();
      } else if(json.code == -1) {
        toastr.error('Thư mục đã tồn tại')
        name.addClass('error');
        modal.hide();
      }
    }
  })
  modal.find('input[name="name_folder"]').val("");
})

function sortFolder() {
  var folderNames =  [];
  $("#modal-upload .modal-content").find("li.tab-folder").each(function(i, e) {
    if (i != 0) {
      folderNames.push(e.dataset.name);
      e.parentNode.removeChild(e);
    }
  });
  folderNames.sort(function(a,b){return a.localeCompare(b)});
  $(folderNames).each(function(i, e) {
    var html = tmpl("tab-folder", e);
    $("#modal-upload").find(".nav-tabs").append(html);
  });
}
// $('#modal-upload').on('show.bs.modal', function() {
//   if (!active) active = 'home';
//   getUpload(active);
// });

// function getUpload(active) {
//   $('#modal-upload').find('div.image').remove();
//   $('#modal-upload .nav-tabs').find('.tab-folder').removeClass('active');
//   $.get('/admin/api/uploads?active=' + active, function(json) {
//     if (!json.code) {
//       var list = '';
//       var htmlFolder = '';
//       var htmlTab = '';
//       $.each(json.data, function(index, elem) {
//         var src = location.origin + '/uploads/' + resizeImage(elem, 240);
//         var obj = {
//           src: src,
//           image: elem
//         };
//         var item = tmpl("item-upload-image", obj);
//         list += item;
//       });
//       $.each(json.arrFolder, function (index, element) {
//         var obj = {
//           name: element
//         }
//         htmlTab += tmpl('item-tab-folder', obj);
//       })
//       $('#modal-upload').find('.add-image').after(list);
//       $('#modal-upload .tab-remove').remove();
//       $('#modal-upload').find('.tab-new-folder').before(htmlTab)
//       $('#modal-upload .nav-tabs .tab-folder').filter('.' + active).addClass('active');
//     }
//   });
// }

$(document).on('click','.image .remove-image',function () {
  var self = $(this);
  var name = self.closest('.item').data('src');
  var active = self.closest('.tab-pane').data('name');
  popupConfirm('Bạn có muốn xóa hình không?',function (result) {
    if (result){
      $.ajax({
        type: 'POST',
        url: '/admin/api/removeUploads',
        data:{
          'name': name,
          'active': active
        },
        success: function (json) {
          if (!json.code){
            toastr.success('Xóa thành công');
            self.closest('.image').remove();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      })
    }
  })
})

$(document).on('click', '.tab-folder .remove-folder', function () {
  var self = $(this);
  var modal = self.closest('.modal')
  var name = self.closest('.tab-folder').data('name');
  var listImage =  modal.find('#' + name + ' .image:not(.add-image)');
  var arrImage = []
  listImage.each(function(){
    var html = $(this).prop('outerHTML')
    arrImage.push(html)
  })
  bootbox.prompt({
    title: "Bạn có muốn xoá thư mục <b>" + name + "</b> không?",
    inputType: 'select',
    inputOptions: [
      {
        text: 'Chọn một hành động...',
        value: '',
      },
      {
        text: 'Xoá thư mục và tất cả hình bên trong',
        value: '1',
      },
      {
        text: 'Xoá thư mục và chuyển hình qua thư mục gốc',
        value: '2',
      }
    ],
    buttons: {
      cancel: {
        label: '<i class="fa fa-times"></i> Huỷ'
      },
      confirm: {
        label: '<i class="fa fa-check"></i> Xoá'
      }
    },
    callback: function (result) {
      if (result){
        $.ajax({
          type: 'POST',
          url: '/admin/api/removeFolder',
          data: {
            'name': name,
            'option': result
          },
          success: function (json) {
            if (!json.code) {
              toastr.success('Xoá thư mục thành công');
              self.closest('.tab-folder').remove();
              modal.find('.tab-pane#' + name).remove();
              modal.find('.nav-tabs .tab-home').addClass('active');
              modal.find('#tab-home').addClass('active in');
              $(".name").text("Home");
              arrImage.forEach(function (element, number) {
                modal.find('#tab-home .list-image').append(element);
              });
            }
            else toastr.error('Thư mục không tồn tại')
          }
        })
      } else if(result == "") {
        toastr.options = {
          "preventDuplicates": true,
          "preventOpenDuplicates": true
        };
        toastr.error('Hãy chọn 1 hành động');
        bootbox.prompt();
      }
    }
  });
})
//Select all
$(".btn-select-all").on("click", function() {
  var tabName = $('.tab-folder.active').data('name');
  tabName = tabName ? tabName : "tab-home";
  var itemList = $(document.getElementById(tabName)).find('div.item');
  if (itemList.length > 0) {
    itemList.each(function(i, e) {
      if ($(".btn-select-all").hasClass("selected-all")) {
        $(this).removeClass("active");
      } else {
        $(this).addClass("active");
      }
    });
    if ($(this).hasClass("selected-all")) {
      $(this).removeClass("selected-all");
      $(this).text("Chọn tất cả");
    } else {
      $(this).addClass("selected-all");
      $(this).text("Bỏ chọn");
    }
  }
})
//Delete selected images
$(".btn-delete").on("click", function() {
  var name = [];
  $('.tab-pane.active').find('.item.active').each(function(i, e) {
    name.push(e.dataset.src);
  })
  var active = $('#modal-upload').find(".tab-pane.active").data('name');
  popupConfirm('Bạn có muốn xóa những hình đã chọn không?',function (result) {
    if (result){
      $.ajax({
        type: 'POST',
        url: '/admin/api/removeUploads',
        data:{
          'name': name,
          'active': active
        },
        success: function (json) {
          if (!json.code){
            toastr.success('Xóa thành công');
            $('#modal-upload').find(".item.active").each(function() {
              $(this).parent().remove();
            })
            resetActions();
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      })
    }
  })
})
//Move image
$('.btn-move').on('click', function() {
  //Get selected images
  var images = [];
  $('#modal-upload').find('.item.active').each(function(i, e) {
    images.push(e.dataset.src);
  });
  //Get current dir
  var currentDir = $('#modal-upload').find(".tab-pane.fade.active");
  if (currentDir.length > 0) {
    currentDir = currentDir[0].dataset.name;
  }
  popupPromptUpload("Di chuyển đến thư mục: ", function(result) {
    var dest = result;//Destination dir
    if (result != null && result.length > 0) {
        $.ajax({
          type: 'POST',
          url: '/admin/api/moveImages',
          data: {
            images: images,
            dest: dest,
            currentDir: currentDir
          },
          success: function(result) {
            toastr.success('Di chuyển thành công ' + result.message + ' hình ảnh');
            if (dest != currentDir) {
              $("#modal-upload").find('.item.active').each(function(i, e) {
                e.parentNode.remove();
              });
              $.ajax({
                type: 'POST',
                url: '/admin/api/getUpload',
                success: function(result) {
                  $.each(result.data, function(i, e) {
                    if (e.name == dest) {
                      $('#modal-upload').find('.tab-pane').each(function(i, e) {
                        if (e.dataset.name == dest) {
                          $(e).find("div.image").each(function(i, e) {
                            e.remove();
                          })
                        }
                      });
                      $.each(e, function(i, element) {
                        if (element != dest)
                        $('#modal-upload').find(".tab-pane").each(function(i, e) {
                          if (e.dataset.name == dest) {
                            var html =   tmpl("item-image", element);
                            $(e).find('.list-image').append(html);
                            $(e).find('.list-image').find('div.image').last().children('.item').last().css("background", "url('/uploads/" + element + " ')");
                          }
                        })
                      })
                    }
                  })
                }
              })
            }
          }
        })
    }
  })
})
function resetActions() {
  $(".btn-select-all").removeClass("selected-all");
  $(".btn-select-all").text("Chọn tất cả");
  $('.tab-content').find('.item').each(function(i, e) {
    $(this).removeClass("active");
  });

}
$('.tab-folder').on('click', function() {
  resetActions();
})
$(".show-folder,.btn-hide-folder").click(function() {
  $(".folder-tree").toggleClass("toggle-folder");
})
$(".tab-content").on("click", ".list-image", function() {
    $(".folder-tree").removeClass("toggle-folder");
    $('.show-folder i').removeClass("glyphicon-chevron-left");
    $('.show-folder i').addClass("glyphicon-chevron-right");
    $('.show-folder').removeClass("hide-folder");
})
$("#modal-upload").on('click', '.tab-home', function() {
  $('#tab-home').addClass("active in");
})


function popupPromptUpload(message, callback) {
  var inputOptions = []
  $("#modal-upload").find(".tab-pane").each(function (i, e) {
    inputOptions.push({ text: e.dataset.name, value: e.dataset.name });
  })
  bootbox.prompt({
    title: message,
    inputType: 'select',
    inputOptions: inputOptions,
    callback: callback
  });
}
