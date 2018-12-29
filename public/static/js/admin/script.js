var staticURI = $('body').data('uri');
var multiLang = $('body').data('multiLang');
var languages = $('body').data('languages');

$(document).find('.treeview.active').find('.treeview-menu').show();
$(document).find('.dt-button-background').remove();
$(document).find('div .dt-buttons').prepend('<a class="dt-button btn-fullscreen"><span>Xem full</span></a>');
var ENABLE_ELFINDER = false;
var ENABLE_NEW_EXPLORER = true;

try {
  EXPLORER = new Explorer({
    iframe_src: "/xpl/finder.html",
    injection_css_url: "/xpl/loader/injection.css",
    iframeSrc: "/xpl/finder.html",
    injectionCssUrl: "/xpl/loader/injection.css"
  });

  EXPLORER.registerHandler('CHOOSE_FILE', function (data) {
    if (!EXPLORER.hasAttr("send-src-to-tinymce")) {
      if (!EXPLORER.getMode("multiple")) {
        var name = data.files[0].name;
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
      } else {
        var imageList = [];
        for (var f of data.files) {
          imageList.push({
            name: f.name,
            src: '/uploads/' + resizeImage(f.name, 480)
          });
        }
        if (EXPLORER.hasAttr("uploadType", "variant")) {
          for (var il of imageList) {
            var html = tmpl("add-item-image", il);
            elementImage.find('.add-image').before(html);
          }
        } else if (EXPLORER.hasAttr("uploadType", "product")) {
          for (var il of imageList) {
            var html = tmpl("add-item-image-product", il);
            elementImage.find('.add-image').before(html);
          }
        } else if (EXPLORER.hasAttr("uploadType", "gallery-photo")) {
          for (var il of imageList) {
            var html = tmpl("item-photo-gallery", il);
            elementImage.find('.list-photos').append(html);
            checkItem();
          }
        }

        EXPLORER.setMode({
          multiple: false
        });
      }
    } else {
      var name = data.files[0].name;
      if (name == undefined) {
        toastr.options = {
          "preventDuplicates": true,
          "preventOpenDuplicates": true
        };
        return toastr.error("Chọn 1 hình");
      }
      $(".mce-reset").first().find('.mce-textbox').first().val("/uploads/" + name);
      EXPLORER.removeCustomAttr("send-src-to-tinymce");
    }

    EXPLORER.close();
  });
  EXPLORER.registerHandler('CLOSE_MODAL', function() {
    EXPLORER.close();
  });
} catch (e) {
  console.log(e.message);
  ENABLE_NEW_EXPLORER = false;
}

function elFinderBrowser(callback, value, meta) {
  tinymce.activeEditor.windowManager.open({
    file: '/elfinder/elfinder.html',// use an absolute path!
    title: 'elFinder 2.1',
    width: 900,
    height: 450,
    resizable: 'yes'
  }, {
    oninsert: function (file, fm) {
      var url, reg, info;

      // URL normalization
      url = fm.convAbsUrl(file.url);

      // Make file info
      info = file.name + ' (' + fm.formatSize(file.size) + ')';

      // Provide file and text for the link dialog
      if (meta.filetype == 'file') {
        callback(url, {text: info, title: info});
      }

      // Provide image and alt text for the image dialog
      if (meta.filetype == 'image') {
        callback(url, {alt: info});
      }

      // Provide alternative source and posted for the media dialog
      if (meta.filetype == 'media') {
        callback(url);
      }
    }
  });
  return false;
}


function initTinymce(item) {
  var tinyOptions = {
    selector: item,
    height: 400,
    theme: 'modern',
    relative_urls: false,
    remove_script_host: false,
    convert_urls: true,
    menubar: false,
    file_picker_types: 'file image media',
    plugins: [
      'code advlist autolink lists link image charmap print preview hr anchor pagebreak',
      'searchreplace wordcount visualblocks visualchars code fullscreen',
      'insertdatetime media nonbreaking save table contextmenu directionality',
      'template paste textcolor colorpicker textpattern imagetools table youtube ',
    ],
    toolbar1: 'formatselect | bold italic underline strikethrough forecolor backcolor | link media | alignleft aligncenter alignright alignjustify |  preview | code',
    toolbar2: 'fontselect fontsizeselect table undo redo  image  youtube | numlist bullist outdent indent  | removeformat fullscreen',
    table_default_styles: {
      width: '50%'
    },
    image_advtab: true,
    automatic_uploads: true,
    images_upload_base_path: '/uploads',
    imageupload_url: '/',
    valid_elements : '*[*]',
    file_browser_callback_types: 'file image',
    file_browser_callback: function (field_name, url, type, win) {
      if (ENABLE_NEW_EXPLORER) {
        EXPLORER.open();
        EXPLORER.setCustomAttr({
          "send-src-to-tinymce": true
        });
      } else {
        $('#modal-upload').modal('show');
        $('#modal-upload').css("z-index", "65538");
        $('#modal-upload').addClass("send-src-to-tinymce");
      }
    },
    setup: function (editor) {
      editor.on('init', function() {
        this.getDoc().body.style.fontSize = '16px';
        this.getDoc().body.style.fontFamily = 'Georgia';
      });
      editor.addButton('alignmentsplit', {
        type: 'splitbutton',
        text: '',
        icon: 'alignleft',
        onclick: function(e) {
          tinyMCE.execCommand(this.value);
        },
        menu: [{
            icon: 'alignleft',
            text: 'Align Left',
            onclick: function() {
              tinyMCE.execCommand('JustifyLeft');
              this.parent().parent().icon('alignleft');
              this.parent().parent().value = 'JustifyLeft'
            }
          }, {
            icon: 'alignright',
            text: 'Align Right',
            onclick: function() {
              tinyMCE.execCommand('JustifyRight');
              this.parent().parent().icon('alignright');
              this.parent().parent().value = 'JustifyRight';
            }
          }, {
            icon: 'aligncenter',
            text: 'Align Center',
            onclick: function() {
              tinyMCE.execCommand('JustifyCenter');
              this.parent().parent().icon('aligncenter');
              this.parent().parent().value = 'JustifyCenter';
            }
          }, {
            icon: 'alignjustify',
            text: 'Justify',
            onclick: function() {
              tinyMCE.execCommand('JustifyFull');
              this.parent().parent().icon('alignjustify');
              this.parent().parent().value = 'JustifyFull';
            }
          }
        ],
        onPostRender: function() {
          // Select the first item by default
          this.value ='JustifyLeft';
        }
      });
    }
  };

  if (window.ENABLE_ELFINDER == true) {
    tinyOptions['file_picker_callback']  = elFinderBrowser;
  }

  if ($(item).length) {
    tinymce.init(tinyOptions);
  }
}

var tbl;

function initDataTable(item) {
  if ($(item).length) {
    tbl = $(item).DataTable({
      aaSorting: [],
      bInfo: true,
      iDisplayLength: getColSortHide('rowShow')?getColSortHide('rowShow'):25,
      bDestroy: true,
      scrollX: $(document).width() > 768 ? true : false,
      order: getColSortHide('colSort') ? [getColSortHide('colSort')] : [],
      bLengthChange: true,
      columnDefs: [{
        targets: 0,
        orderable: false,
        order: []
      }, ],
      responsive: $(document).width() < 768 ? true : false,
      rowReorder: false,
      fnRowCallback: function (a, b, c, d) {
        var img = $(a).find('img')[0];
        var dataSrc = $(img).data('src');
        if (dataSrc) $(img).attr('src', dataSrc);
      },
      dom: 'Blfrtip',
      buttons: [{
          text: 'Xuất excel',
          exportOptions: {
            columns: ':visible'
          },
          action: function (e, dt, button, config) {
            var data = dt.buttons.exportData({
              columns: ':visible :not(:first-child)'
            });

            data.body.unshift(data.header);

            $.ajax({
              url: '/admin/api/exportExcel',
              type: 'POST',
              data: {
                products: JSON.stringify(data.body),
                name: modelName
              },
              success: function (json) {
                if (json.success) {
                  downloadURI(json.url);
                }
              }
            });
          }
        },
        {
          extend: 'print',
          exportOptions: {
            columns: ':visible :not(:first-child)',
            stripHtml: true
          }
        },
        {
          extend: 'colvis',
          columns: ':not(:first-child)'
        }
      ],
      language: {
        buttons: {
          print: 'In',
          colvis: 'Ẩn/hiện cột',
        },
        sSearch: "Tìm kiếm: ",
        searchPlaceholder: 'Nhập từ khóa cần tìm...',
        sLengthMenu: "Hiển thị _MENU_ dữ liệu",
        info: "Hiển thị từ _START_ đến _END_ trên tổng _TOTAL_ dữ liệu",
        sEmptyTable: "Không có dữ liệu để hiển thị",
        oPaginate: {
          sFirst: "Đầu",
          sLast: "Cuối",
          sNext: "Sau",
          sPrevious: "Trước",
        },
      },
      search: {
        regex: true
      }
    });
    tbl.columns(getColSortHide('colHide') ? getColSortHide('colHide') : []).visible(false);

    $('table').on('order.dt', function () {
      var order = tbl.order();
      setColSort(order[0][0], order[0][1]);
    });
    $('table').on('column-visibility.dt', function (e, settings, column, state) {
      setColHide(column);
    });
  }
}


function initDataTableAjax(item, url_source, url_export_excel) {
  if ($(item).length) {
    tbl = $(item).DataTable( {
        processing: true,
        serverSide: true,
        scrollX: $(document).width() > 768 ? true : false,
        responsive: $(document).width() < 768 ? true : false,
        rowReorder: false,
        fnRowCallback: function (a, b, c, d) {
          var img = $(a).find('img')[0];
          var dataSrc = $(img).data('src');
          if (dataSrc) $(img).attr('src', dataSrc);
        },
        dom: 'Blftipr',
        processing: true,
        ajax: url_source,
        //Set column definition initialisation properties.
        columnDefs: [{
          targets: [0],
          orderable: false,
          order: []
        }, ],
        buttons: [{
            text: 'Xuất excel',
            exportOptions: {
              columns: ':visible'
            },
            action: function (e, dt, button, config) {
              var data = dt.buttons.exportData({
                columns: ':visible :not(:first-child)'
              });

              data.body.unshift(data.header);

              $.ajax({
                url: url_export_excel,
                type: 'POST',
                success: function (json) {
                  if (json.success) {
                    downloadURI(json.url);
                  }
                  else {
                    console.log(json);
                  }
                }
              });
            }
          },
          {
            extend: 'print',
            exportOptions: {
              columns: ':visible :not(:first-child)',
              stripHtml: true
            }
          },
          {
            extend: 'colvis',
            columns: ':not(:first-child)'
          }
        ],
        language: {
          buttons: {
            print: 'In',
            colvis: 'Ẩn/hiện cột',
          },
          sSearch: "Tìm kiếm: ",
          searchPlaceholder: 'Nhập từ khóa cần tìm...',
          sLengthMenu: "Hiển thị _MENU_ dữ liệu",
          info: "Hiển thị từ _START_ đến _END_ trên tổng _TOTAL_ dữ liệu",
          sEmptyTable: "Không có dữ liệu để hiển thị",
          processing: "Dữ liệu đang được tải...",
          oPaginate: {
            sFirst: "Đầu",
            sLast: "Cuối",
            sNext: "Sau",
            sPrevious: "Trước",
          },
        }
    } );
    tbl.columns(getColSortHide('colHide') ? getColSortHide('colHide') : []).visible(false);

    $('table').on('order.dt', function () {
      var order = tbl.order();
      setColSort(order[0][0], order[0][1]);
    });
    $('table').on('column-visibility.dt', function (e, settings, column, state) {
      setColHide(column);
    });
  }
}

function downloadURI(uri) {
  var link = document.createElement("a");
  link.href = uri;
  link.click();
}

//set row show
$(document).on('change', '.dataTables_length select',function () {
  var num = $(this).val();
  var pathName = location.pathname;
  var storage = [];
  if (localStorage.getItem('rowShow')) {
    storage = JSON.parse(localStorage.getItem('rowShow'));
    storage[pathName] = num;
  } else {
    storage[pathName] = num;
  }
  localStorage.setItem('rowShow', JSON.stringify($.extend({}, storage)));
})

function checkExtImage(image) {
  var ext = image.split('.').pop().toLowerCase();
  if ($.inArray(ext, ['png', 'jpg', 'jpeg', 'gif', 'svg', 'ico']) == -1) {
    toastr.error('Vui lòng chọn đúng định dạng hình ảnh');
    return 0;
  }
  return 1;
}

function resizeImage(image, size) {
  if (image) {
    var ext = image.split('.').pop();
    var a = '.' + ext;
    var b = '_' + size + a;
    var c = image.replace(a, b);
    return c;
  }
  return image;
}

$('#modal-change-password').on('show.bs.modal', function () {
  $('#modal-change-password').find('input').val('');
});

$('.form-change-password').on('submit', function (e) {
  e.preventDefault();
  var password = $(this).find('input[name="password"]').val();
  var new_password = $(this).find('input[name="new_password"]').val();
  var re_new_password = $(this).find('input[name="re_new_password"]').val();
  if (new_password != re_new_password) {
    toastr.error('Mật khẩu nhập lại không khớp');
    self.find('input[name="re_new_password"]').addClass('error');
    return;
  }
  $.ajax({
    type: 'PUT',
    url: '/admin/api/user/changePassword',
    data: {
      password: password,
      new_password: new_password
    },
    success: function (json) {
      if (!json.code) {
        toastr.success('Đổi mật khẩu thành công');
        setTimeout(function () {
          window.location.reload();
        }, 1000);
      } else if (json.code == -1) toastr.error('Mật khẩu cũ không đúng');
      else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
    }
  });
});

$('.btn-remove-image').click(function () {
  var image = $(this).data('image');
  $(this).closest('.form-group').find('input').val('');
  $(this).closest('.form-group').find('img').attr('src', '/static/img/' + image);
});

function createHandle(str) {
  if (str) {
    str = str + '';
    str = str.trim();
    str = str.toLowerCase();
    str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
    str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
    str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
    str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
    str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
    str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
    str = str.replace(/đ/g, "d");
    str = str.replace(/\,/g, '-');
    str = str.replace(/\./g, '-');
    str = str.replace(/\!/g, '-');
    str = str.replace(/\?/g, '-');
    str = str.replace(/\~/g, '-');
    str = str.replace(/\ /g, '-');
    str = str.replace(/\|/g, '-');
    str = str.replace(/\./g, '-');
    str = str.replace(/\"/g, '-');
    str = str.replace(/\'/g, '-');
    str = str.replace(/\-\-+/g, '-');
    str = str.replace(/\s+/g, '-');
    str = str.replace(/[^\w\-]+/g, '');
    str = str.replace(/\-\-+/g, '-');
    str = str.replace(/^-+/, '');
    str = str.replace(/-+$/, '');
    if (str.slice(-1) == '-') str = str.substring(0, str.length - 1);
  }
  return str;
}

function checkDate(datetime) {
  return new Date(datetime);
}

function reloadPage(url = null) {
  setTimeout(function () {
    if (url) location.href = url;
    else location.reload();
  }, 1000);
}

$(window).on('load', function () {

  $("select[data-value]").each(function () {
    var value = $(this).data('value');
    if ($(this).hasClass('chosen-select')) $(this).val(value).trigger('chosen:updated');
    else $(this).val(value);
    if ($(this).attr('name') == 'region') {
      if ($('select[name="region"]').length && $('select[name="subregion"]').length) {
        changeRegion();
      }
    }
  });

  var first_init_select = true;
  function changeRegion() {
    var region_id = $('select[name="region"]').val();
    StoreAPI.getSubRegion(region_id, function (result) {
      if (result.data && result.data.length) {
        var options = '';
        $.each(result.data, function (index, element) {
          options += '<option value="' + element.id + '">' + element.name + '</option>';
        });
        $('select[name="subregion"]').html(options);

        if (first_init_select) {
          first_init_select = false;
          var value = $('select[name="subregion"]').data('value');
          if (value) {
            $('select[name="subregion"]').val(value);
            $('select[name="subregion"]').attr('data-value', '');
          }
        }
      }
    });
  }

  $('select[name="region"]').change(changeRegion);


  $(".datepicker").datepicker({
    dateFormat: 'dd-mm-yy',
    minDate: 0,
    defaultDate: "+1w",
    changeMonth: true
  });

  $(".start-date").datepicker({
    dateFormat: 'dd-mm-yy',
    minDate: 0,
    defaultDate: "+1w",
    changeMonth: true,
    onSelect: function (selected) {
      var start_date = $(".start-date").datepicker("getDate");
      $(".end-date").datepicker("option", "minDate", start_date);
    }
  }).on('clearDate', function (selected) {
    $('.end-date').datepicker("option", "minDate", 0);
  });

  $(".end-date").datepicker({
    dateFormat: 'dd-mm-yy',
    minDate: 0,
    defaultDate: "+1w",
    changeMonth: true,
    onSelect: function (selected) {
      var end_date = $(".end-date").datepicker("getDate")
      $(".start-date").datepicker("option", "maxDate", end_date);
    }
  }).on('clearDate', function (selected) {
    $('.start-date').datepicker("option", "maxDate", null);
  });
});

$(document).on('click', '.main-item', function() {
  var treeview = $(this).closest('.treeview');
  treeview.siblings().removeClass('active');
  treeview.siblings().find('.treeview-menu').slideUp(300);
  if (treeview.hasClass('active')) {
    treeview.find('.treeview-menu').slideUp(300);
    treeview.removeClass('active');
  } else {
    treeview.find('.treeview-menu').slideDown(300);
    treeview.addClass('active');
  }
});

$(document).on('click', '.main-sidebar .treeview-menu a', function(e) {
  e.stopPropagation();
});

function popupConfirm(message, callback) {
  bootbox.confirm({
    message: message,
    buttons: {
      confirm: {
        label: '<i class="fa fa-check"></i> Đồng ý',
        className: 'btn-primary'
      },
      cancel: {
        label: '<i class="fa fa-times"></i>  Hủy',
        className: 'btn-default'
      }
    },
    callback: callback
  });
}

function popupPrompt(message, inputType, callback) {
  bootbox.prompt({
    title: message,
    inputType: inputType,
    buttons: {
      confirm: {
        label: '<i class="fa fa-check"></i> Đồng ý',
        className: 'btn-primary'
      },
      cancel: {
        label: '<i class="fa fa-times"></i>  Hủy',
        className: 'btn-default'
      }
    },
    callback: callback
  });
}

function updateSEO(type, type_id) {
  var data = {};
  data.meta_title = $('input[name="meta_title"]').val();
  data.meta_description = $('textarea[name="meta_description"]').val();
  data.meta_keyword = $('textarea[name="meta_keyword"]').val();
  data.meta_robots = $('select[name="meta_robots"]').val();
  data.meta_image = $('input[name="meta_image"]').val();

  data.multiLang = [];

  if (languages) {
    $.each(languages, function(index, elem) {
      var obj = {};
      obj.lang = elem;
      obj.id = $('input[name="translattion_seo_' + elem + '"]').val()
      obj.meta_title = $('input[name="meta_title_' + elem + '"]').val()
      obj.meta_description = $('textarea[name="meta_description_' + elem + '"]').val();
      obj.meta_keyword = $('textarea[name="meta_keyword_' + elem + '"]').val();
      data.multiLang.push(obj);
    });
  }

  $.ajax({
    type: 'POST',
    url: '/admin/api/seo',
    data: {
      type: type,
      type_id: type_id,
      data: data
    },
    success: function (json) {
      if (!json.code) console.log("success");
    }
  });
}
function updateMetafield(post_type, post_id) {

  if (post_type == 'product_attribute') {
    if ($('.list-attributes').find('.item-attribute').length) {
      var data = {};
      $('.list-attributes .item-attribute').each(function (index, elem) {
        var item = {};
        item.title = $(this).data('title');
        item.handle = createHandle(item.title);
        item.post_id = post_id;
        item.post_type = post_type;
        item.value = $(this).find('.value').val();
        item.value = JSON.stringify(item.value);
        data[index] = item;
      });
      $.post('/admin/metafield', data, function (res) {
        if (!res.code) {
          console.log("Save metafield success");
        }
      });
    }
  }
   else {
    if ($('.box-custom-field').length) {
      var data = {};
      $('.box-custom-field').find('.form-group:not(.excluded)').each(function (index, elem) {
        var self = $(this);
        var item = {};
        var inputType = $(this).data('input_type');
        item.title = $(this).data('title');
        item.handle = $(this).data('handle');
        item.post_id = post_id;
        item.post_type = post_type;
        if (inputType == 'images') {
          item.value = [];
          $(this).find('.list-image .image').each(function () {
            item.value.push($(this).attr('data-name'));
          });
          item.value = JSON.stringify(item.value);
        }
        else if (inputType == 'map') {
          var map_value = {};
          map_value.latLng = $(this).find('.box-object-setting').attr('data-value');
          map_value.address = $(this).find('.value').val();
          item.value = '[' + JSON.stringify(map_value) + ']';
        }
        else if (inputType == 'datetime') {
          var time = $(this).find('input[name="publish_time"]').val();
          var date = $(this).find('input[name="publish_date"]').val();
          date = dmy2ymd(date);
          item.value = date + ' ' + time;
        }
        else {
          item.value = $(this).find('.value').val();
        }

        if ($(this).find('.value').hasClass('chosen-select')) {
          item.value = JSON.stringify(item.value);
        } else if ($(this).find('.value').hasClass('editor')) {
          var id = $(this).find('.value').attr('id');
          item.value = tinyMCE.get(id).getContent();
        } else if ($(this).find('.value').hasClass('checkbox')) {
          item.value = $(this).find('.value').is(':checked');
        }

        if (languages && (inputType == 'input' || inputType == 'textarea' || inputType == 'editor' || inputType == 'image')) {
          item.multiLang = [];
          $.each(languages, function (index, elem) {
            var obj = {};
            obj.lang = elem;
            if (inputType == 'textarea') {
              obj.value = $("textarea[name='" + item.handle +  '-' + elem + "']").val();
            }
            else {
              obj.value = $("input[name='" + item.handle +  '-' + elem + "']").val();
            }
            if (self.find('.value').hasClass('editor')) {
              var idLang = self.find('.value').attr('id');
              obj.value = tinyMCE.get(idLang + '-' + elem).getContent();
            }
            item.multiLang.push(obj);
          });
        }
        data[index] = item;
      });
      $.post('/admin/metafield', data, function (res) {
        if (!res.code) {
          console.log("Save metafield success");
        }
      });
    }
  }
}

function dmy2ymd(date) {
  if (date.indexOf('-') > -1) {
    var arr = date.split('-');
    return arr[2] + '-' + arr[1] + '-' + arr[0];
  } else if (date.indexOf('/') > -1) {
    var arr = date.split('/');
    return arr[2] + '/' + arr[1] + '/' + arr[0];
  }
  return date;
}

$(document).on('click', 'table :checkbox', function () {
  if ($(this).hasClass('select-all')) {
    $('tbody').find(':checkbox').prop('checked', $(this).is(':checked'));
  }
  if ($('tbody input:checkbox:checked').length) {
    $('.action-box').removeClass('hidden');
    var numberOfChecked = $('tbody input:checkbox:checked').length;
    $('.num-select').html(' ' + numberOfChecked + ' ');
  } else $('.action-box').addClass('hidden');
})

$(document).on('click', '.btn-fullscreen', function () {
  var self = $(this);
  $('.box-table').toggleClass('fullscreen');
  if ($('.box-table').hasClass('fullscreen')) {
    self.text('Tắt xem full');
  } else {
    self.text('Xem full');
  }
})

$(document).on('click', '.status-active', function () {
  var arrId = [];
  $('tbody input:checkbox:checked').each(function () {
    arrId.push($(this).val());
  })
  $.ajax({
    type: 'POST',
    url: '/admin/api/updateStatus',
    data: {
      type: modelName,
      arrId: arrId,
      status: 'inactive'
    },
    success: function (json) {
      $('tbody input:checkbox:checked').each(function () {
        $(this).closest('tr').find('td:last-of-type').html('<label class="label label-warning">Đang ẩn</label>');
        $(this).closest('tr').parent().find("tr.child").find(".dtr-data").last().html('<label class="label label-warning">Đang ẩn</label>');
      })
      toastr.success('Cập nhật trạng thái thành công!');
      if ($(window).width() < 1035) {
        location.reload();
      }
    }
  })
})
$(document).on('click', '.status-inactive', function () {

  var arrId = [];

  $('tbody input:checkbox:checked').each(function () {
    arrId.push($(this).val());
  });

  $.ajax({
    type: 'POST',
    url: '/admin/api/updateStatus',
    data: {
      type: modelName,
      arrId: arrId,
      status: 'active'
    },
    success: function (json) {
      $('tbody input:checkbox:checked').each(function () {
        $(this).closest('tr').find('td:last-of-type').html('<label class="label label-info">Đang hiện</label>');
        $(this).closest('tr').parent().find("tr.child").find(".dtr-data").last().html('<label class="label label-info">Đang hiện</label>');
      })
      toastr.success('Cập nhật trạng thái thành công!');
      if ($(window).width() < 1035) {
        location.reload();
      }
    }
  })
})
$(document).on('click', '.status-delete', function () {
  var arrId = [];
  $('tbody input:checkbox:checked').each(function () {
    arrId.push($(this).val());
  })
  var numSelected = $('tbody input:checkbox:checked').length;
  popupConfirm('Bạn có muốn xóa ' + numSelected + ' mục đã chọn không?', function (result) {
    if (result) {
      $.ajax({
        type: 'POST',
        url: '/admin/api/updateStatus',
        data: {
          type: modelName,
          arrId: arrId,
          status: 'delete'
        },
        success: function (json) {
          if (!json.code) {
            $('tbody input:checkbox:checked').each(function () {
              $(this).closest('tr').remove();
            })
            toastr.success('Xoá thành công!');
            if ($(window).width() < 1035 || modelName == 'order') {
              location.reload();
            }
          } else {
            toastr.error('Bạn không có quyền xoá mục này!');
          }
        }
      })
    }
  })
});

function formatMoney(num) {
  if (num) num = num.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") + 'đ';
  return num;
}

function _formatMoney(num) {
  if (num) num = num.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
  return num;
}

autoResizeTextarea();

function autoResizeTextarea() {
  $('textarea[data-autoresize]').each(function () {
    var offset = this.offsetHeight - this.clientHeight;
    var resizeTextarea = function (item) {
      $(item).css('height', 'auto').css('height', item.scrollHeight + offset);
    };
    resizeTextarea(this);
    $(this).on('keyup input focus change', function () {
      resizeTextarea(this);
    }).removeAttr('data-autoresize');
  });
}

$(document).on('click', 'a.tab-lang', function () {
  var id = $(this).attr('href');
  if (!$(this).hasClass('change')) {
    setTimeout(function () {
      $(id).find('textarea').trigger('change');
    }, 300);
    $(this).addClass('change');
  }
});

function setColSort(column, type) {
  var table = [column, type];
  var pathName = location.pathname;
  var storage = [];
  if (localStorage.getItem('colSort')) {
    storage = JSON.parse(localStorage.getItem('colSort'));
    storage[pathName] = table;
  } else {
    storage[pathName] = table;
  }
  localStorage.setItem('colSort', JSON.stringify($.extend({}, storage)));
  return;
}

function setColHide(column) {
  var pathName = location.pathname;
  var col = [column];
  if (localStorage.getItem('colHide')) {
    var storage = JSON.parse(localStorage.getItem('colHide'));
    if (storage[pathName]) {
      var arrCol = storage[pathName];
      var num = arrCol.indexOf(column);
      if (num == -1) {
        arrCol.push(column);
      } else {
        arrCol.splice(num, 1);
      }
      storage[pathName] = arrCol;
    } else {
      storage[pathName] = col;
    }
    localStorage.setItem('colHide', JSON.stringify($.extend({}, storage)));
    return;
  }
  var temp = [];
  temp[pathName] = col;
  localStorage.setItem('colHide', JSON.stringify($.extend({}, temp)));
  return;
}

function getColSortHide(type) {
  if (!localStorage.getItem(type)) return 0;
  var pathName = location.pathname;
  var storage = JSON.parse(localStorage.getItem(type));
  if (storage[pathName] != null) {
    var result = storage[pathName];
    return result;
  }
  return 0;
}

$("input").keyup(function () {
  if ($(this).hasClass('error')) {
    $(this).removeClass('error');
  }
});

$("input[name='priority']").keyup(function (evt) {
  var input = $(this).val();
  var theEvent = evt || window.event;
  var key = theEvent.keyCode || theEvent.which;
  key = String.fromCharCode(key);
  var regex = /[0-9]|\+|-|\./;
  if (!regex.test(key)) {
    theEvent.returnValue = false;
    if (theEvent.preventDefault) theEvent.preventDefault();
  }
  input = input.match(/^(-|\+)?[0-9]*/g);
  $(this).val(input);
})

function inputPositiveNumbers(evt) {
  var theEvent = evt || window.event;
  var key = theEvent.keyCode || theEvent.which;
  key = String.fromCharCode(key);
  var regex = /[0-9]|\./;
  if(evt.target.value.length > 11) theEvent.returnValue = false;
  if (!regex.test(key)) {
    theEvent.returnValue = false;
    if (theEvent.preventDefault) theEvent.preventDefault();
  }
}

// Automatically Format User Input
$(document).on('keyup', '.formatMoney',function() {
  strToPrice($(this).val());
  var selection = window.getSelection().toString();
			if ( selection !== '' ) {
				return;
			}
			// When the arrow keys are pressed, abort.
			if ( $.inArray( event.keyCode, [38,40,37,39] ) !== -1 ) {
				return;
			}
			var $this = $( this );

			// Get the value.
			var input = $this.val();

			var input = input.replace(/[\D\s\._\-]+/g, "");
          input = input.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
					$this.val( function() {
						return input;
					} );
});

// Sanitize the values.
function strToPrice(price){
  if (price)  {
    price = price.replace(/,/g, '');
    return parseInt(price);
  }
  return price;
}
//numver to String format money
function numToStringPrice(num){
  if(num) return  num.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
}

// tags

$('.list-tags').on('click', '.tag-name', function () {
  var text = $(this).html();
  $("input[name='tags']").tagsinput('add', text);
});
$('.list-authors').on('click', '.author-name', function () {
  var text = $(this).html();
  $("input[name='author']").tagsinput('removeAll');
  $("input[name='author']").tagsinput('add', text);
});

// auto complete tags

var elt = $('input[name="tags"]');
elt.tagsinput({
  maxChars: 50,
  trimValue: true,
  typeahead: {
    source:function(query){
      var array = [];
      $.ajax({
          type: 'GET',
          async:false,
          url: "/admin/api/tag?input=" + query,
          success: function(data) {
              array = data.id.map(function(item) { return item.name });
          }
        })
        return array;
    },
    afterSelect: function() {
    	this.$element[0].value = '';
    }
  }
});

function isEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9])+$/;
  return regex.test(email);
}

function isPhone(phone) {
  var regex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im;
  return regex.test(phone);
}

$('.box-info').on('change', '.title', function() {
  var self = $(this);
  var title = self.val();
  var handle = createHandle(title);
  var parent = self.closest('.tab-pane');
  var lang = parent.attr('data-lang') || 'vi';
  checkPostHandle(handle, lang, function (res) {
    parent.find('.handle').val(res);
  });
});


$('.box-basic-info').on('change', '.title', function () {
  var self = $(this);
  var title = self.val();
  var handle = createHandle(title);
  var parent = self.closest('.tab-pane');
  var lang = parent.attr('data-lang') || 'vi';
  checkPostHandle(handle, lang, function (res) {
    parent.find('.handle').val(res);
  });
});


$('.box-info').on('change', '.handle', function () {
  var self = $(this);
  var parent = self.closest('.tab-pane');
  var handle = $(this).val();
  handle = createHandle(handle);
  var old_handle = self.attr('data-handle');
  var lang = parent.attr('data-lang') || 'vi';
  if (old_handle && handle == old_handle) {
    self.val(handle);
  } else {
    checkPostHandle(handle, lang, function (res) {
      self.val(res);
    });
  }
  parent.find('input.title').removeClass('title');
});

$('.box-basic-info').on('change', '.handle', function () {
  var self = $(this);
  var parent = self.closest('.tab-pane');
  var handle = $(this).val();
  handle = createHandle(handle);
  var old_handle = self.attr('data-handle');
  var lang = parent.attr('data-lang') || 'vi';
  if (old_handle && handle == old_handle) {
    self.val(handle);
  } else {
    checkPostHandle(handle, lang, function (res) {
      self.val(res);
    });
  }
  parent.find('input.title').removeClass('title');
});

$('.icon-refresh-handle').click(function() {
  var parent = $(this).closest('.tab-pane');
  var input = $(this).parent().find('input');
  var lang = parent.attr('data-lang') || 'vi';
  var input_name = 'title';
  if (lang != 'vi') input_name = 'title_' + lang;
  var title = parent.find('input[name='+input_name+']').val();
  if (title) {
    var handle = createHandle(title);
    var old_handle = input.attr('data-handle');
    if (old_handle && handle == old_handle) {
      input.val(handle);
    } else {
      checkPostHandle(handle, lang, function (res) {
        input.val(res);
      });
    }
  }
});

if (location.href.indexOf('/create') > 0) {

  $('.box-seo').on('change', 'input, textarea', function() {
    if ($(this).val()) $(this).addClass('changed');
  });

  $(document).on('change', 'input[name^="title"]', function() {
    var parent = $(this).closest('.tab-pane');
    var name = $(this).attr('name');
    var value = $(this).val();
    var meta = 'meta_' + name;
    var input = $('input[name="' + meta + '"]');
    if (!input.hasClass('changed')) {
      input.val(value);
    }
  });

  $(document).on('change', 'textarea[name^="description"]', function() {
    var parent = $(this).closest('.tab-pane');
    var name = $(this).attr('name');
    var value = $(this).val();
    var meta = 'meta_' + name;
    var textarea = $('textarea[name="' + meta + '"]');
    if (!textarea.hasClass('changed')) {
      textarea.val(value);
    }
  });
}

function checkPostHandle(handle, lang, callback) {
  var url = '/admin/api/checkPostHandle?handle=' + handle + '&lang=' + lang;
  $.get(url, callback);
}

$(document).on('click', '.btn-upload-file', function () {
  var parent = $(this).parent();
  var input = parent.find('input[type="file"]');
  var file_data = input.prop('files')[0];
  var form_data = new FormData();
  form_data.append('file', file_data);
  $(this).addClass('disabled');
  $.ajax({
    type: 'POST',
    url: '/admin/api/uploadFile',
    cache: false,
    contentType: false,
    processData: false,
    data: form_data,
    success: function (res) {
      parent.find('.disabled').removeClass('disabled');
      if (!res.code) {
        parent.find('.value').val(res.name);
        parent.find('.link').remove();
        parent.append('<p class="link" style="margin-top: 10px;">Link file: <a target="_blank" href="' + res.link + '">' + res.link + '</a><i class="remove-file ico-times"></i></p>');
      } else {
        parent.append('<p class="link" style="margin-top: 10px; color: red;">'+res.message+'</p>');
      }
    }
  });
});
$(document).on('click', '.remove-file', function(){
  $(this).closest('.upload-file').find('.value').val('');
  $(this).parent().remove();
})



// 6-11-2018

$(document).on('click', '.btn-delete-object', function() {
  var id = $(this).attr('data-id');
  var type = $(this).attr('data-type');
  var name = '';
  switch (type) {
    case 'product':
      name = 'sản phẩm';
      break;
    case 'collection':
      name = 'nhóm sản phẩm';
      break;
    case 'article':
      name = 'bài viết';
      break;
    case 'blog':
      name = 'nhóm bài viết';
      break;
    case 'page':
      name = 'page';
      break;
    case 'coupon':
      name = 'mã giảm giá';
      break;
    case 'role':
      name = 'phân quyền';
      break;
    case 'sale':
      name = 'khuyễn mãi';
      break;
    case 'user':
      name = 'người dùng';
      break;
    case 'bank':
      name = 'ngân hàng';
      break;
    default:
      name = 'mục';
  }

  popupConfirm('Bạn có muốn xóa '+ name +' hiện tại không?', function (result) {
    if(result) {
      $.ajax({
        type: 'DELETE',
        url: '/admin/'+ type +'/' + id,
        success: function(json) {
          if(!json.code) {
            toastr.success('Xóa '+ name +' thành công');
            setTimeout(function () {
              location.href = '/admin/' + type;
            }, 1000);
          } else toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
        }
      });
    }
  });
});

(function(){
  $('.order .icon-number-sidebar').each(function(index, el) {
    var self = $(this);
    var status = self.attr('data-type');
    $.get('/admin/api/order/status?status=' + status, function (res) {
      var count = res.count || 0;
      if (count) {
        self.html(count);
        self.show();
      }
    });
  });

  $.get('/admin/api/contact/status?read=0', function(res) {
    var count = res.count || 0;
    if (count) {
      $('.sidebar .contact .icon-number-sidebar[data-type=new]').html(count);
      $('.sidebar .contact .icon-number-sidebar[data-type=new]').show();
    }
  });
})();

var group_index = 0;

function renderComponent(settings) {

  if (settings && settings.tabs) {

    var arr_nav_pills = [];
    var tab_content = '';

    $.each(settings.tabs, function(index, tab) {
      arr_nav_pills.push({
        class: index ? '' : 'active',
        id: tab.id,
        title: tab.title
      });

      if (tab.groups && tab.groups.length) {
        var groups = '';
        $.each(tab.groups, function(indexG, group) {
          var attributes = '';

          var group_index = index * 100 + indexG * 10;
          var default_lang = '';
          if (group.multi_language && languages && languages.length) {
            var default_lang = '_vi';
          }

          if (group.attributes && group.attributes.length) {
            attributes = tmpl('setting-attributes', {
              attributes: group.attributes,
              lang: default_lang
            });
          }

          if (group.multi_language && languages && languages.length) {

            var arr_lang = [];
            var arr_tab_content_multi_lang = '';
            var arr_type_multi_lang = ['text', 'textarea', 'editor', 'image', 'tag'];

            $.each(languages, function(index, elem) {
              var title = 'Tiếng Anh';
              if (elem == 'jp') title = 'Tiếng Nhật';
              else if (elem == 'ko') title = 'Tiếng Hàn';
              arr_lang.push({
                code: elem,
                title: title
              });
            });

            if (group.attributes && group.attributes.length) {

              $.each(languages, function (index, element) {
                group_index++;
                var attributes_multi_lang = '';
                $.each(group.attributes, function (index, elem) {
                  if (arr_type_multi_lang.indexOf(elem.type) > -1) {
                    attributes_multi_lang += tmpl('setting-attributes-multi-lang', {
                      attribute: elem,
                      lang: element
                    });
                  }
                });

                arr_tab_content_multi_lang += tmpl('setting-tab-pane-content-multi-lang', {
                  id: group_index,
                  attributes_multi_lang: attributes_multi_lang
                });
              });
            }

            var tab_pane_multi_lang = tmpl('setting-tab-pane-multi-lang', {
              id: group_index,
              attributes: attributes,
              arr_tab_content_multi_lang: arr_tab_content_multi_lang,
              languages: arr_lang
            });

            groups += tmpl('setting-box-item-multi-lang', {
              title: group.title,
              languages: arr_lang,
              id: group_index,
              tab_pane_multi_lang: tab_pane_multi_lang
            });

          } else {
            groups += tmpl('setting-box-item', {
              title: group.title,
              attributes: attributes
            });
          }
        });
      }

      tab_content += tmpl('setting-tab-pane', {
        class: index ? '' : 'in active',
        id: tab.id,
        groups: groups
      });

    });

    var nav_pills = tmpl('setting-nav-pills', {
      items: arr_nav_pills
    });

    var contents = tmpl('section-settings', {
      nav_pills: nav_pills,
      tab_content: tab_content
    });

    return contents;

  }
}
(function (d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src =
    "https://sdk.designbold.com/button.js#app_id=5d46ebb068";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'db-js-sdk'));

window.DBSDK_Cfg = {
  export_mode: ['download', 'preview', 'publish'],
  export_callback: function (resultUrl, documentId, exportTarget) {
    uploadImageURL(resultUrl);
  }
};

// Remove image customfield in list image
$(document).on('click', '.box-custom-field .list-image span.remove', function() {
  $(this).parent().remove();
});

$('#tab-seo-vi select[name="meta_robots"]').on('change', function() {
  var value = $(this).val();
  $('.box-seo').find('select[name="meta_keyword_other"]').val(value);
});
$(document).on('change', '#tab-seo-vi input[name="meta_image"]', function() {
  var value = $(this).val();
  $('.box-seo').find('.meta_images_other img').attr('src', '/uploads/' + resizeImage(value, 480));
});



// Active current tab setting
$(document).on('shown.bs.tab', 'a[data-toggle="pill"]', function (e) {
  localStorage.setItem('activeTab', $(e.target).attr('href'));
})

$(window).on('load', function() {
  var activeTab = localStorage.getItem('activeTab');
  if (activeTab) {
    $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
  }
});
