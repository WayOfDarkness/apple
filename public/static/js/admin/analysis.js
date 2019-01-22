var content_apprecitation = [];

$('#appreciation-hidden').hide();
$('#appreciation-wrapper .control-label').hide();
var html = $('#custom-appreciation-wrapper').html();
$('#appreciation-wrapper').append( html )




function tmplHtmlAppreciation(data) {
  return `<tr>
          <td><i class="fa fa-arrows" aria-hidden="true"></i></td>
          <td>${data.value}</td>
          <td><span class="btn-remove btn-remove-appreciation" data-id="${data.id}"><i class="fa fa-trash"></i></span>
        </tr>`
}


function reloadAppreciation(content, type){
  if (!Array.isArray(content)) {
    return;
  }


  var html = content.map(function(val) {
    return tmplHtmlAppreciation(val);
  }).join('');
  $('#'+ type +'-content').html(html);

  $('.box-custom-field textarea[name="appreciation"]').val(JSON.stringify(content_apprecitation));
  $('#appreciation-wrapper textarea[name="advantages"]').val('');
  $('#appreciation-wrapper textarea[name="disadvantages"]').val('');
}

function addObjectToArr(arr, data){
  var count = 0;
  $.each(arr,function(inx, item){
    if (item.value == data.value) {
      count++ ;
    }
  })
  if (!count) {
    arr.unshift(data);
    return;
  }
  return;
}




// // discount custom
  var arr_appreciation = [];
  var arr_advantages = [];
  var arr_disadvantages = [];
  arr_appreciation = $('.box-custom-field textarea[name="appreciation"]').val();
  if(arr_appreciation){
    content_apprecitation = JSON.parse(arr_appreciation);
    arr_advantages = content_apprecitation[0] ? content_apprecitation[0] : [];
    arr_disadvantages = content_apprecitation[1] ? content_apprecitation[1] : [];

  }else {
    var content_apprecitation = [];
  }


  reloadAppreciation(arr_advantages, 'advantages');
  reloadAppreciation(arr_disadvantages, 'disadvantages');

  $('.btn-add-advantages').on('click', function() {
    var data = {};
    data.type = 'advantages';
    data.value = $('textarea[name="advantages"]').val();

    if(!data.value){
      toastr.error('Chưa nhập Nội dung');
      return;
    }

    var nowTime = new Date();
    data.id =nowTime.getTime();
    data.status = 'active';

    addObjectToArr(arr_advantages, data);
    content_apprecitation[0] = arr_advantages;
    reloadAppreciation(arr_advantages, 'advantages');
  })

  $('.btn-add-disadvantages').on('click', function() {
    var data = {};
    data.type = 'advantages';
    data.value = $('textarea[name="disadvantages"]').val();

    if(!data.value){
      toastr.error('Chưa nhập Nội dung');
      return;
    }

    var nowTime = new Date();
    data.id =nowTime.getTime();
    data.status = 'active';

    addObjectToArr(arr_disadvantages, data);
    content_apprecitation[1] = arr_disadvantages;
    reloadAppreciation(arr_disadvantages, 'disadvantages');
  })


  $(document).on('click','#advantages-content td .btn-remove-appreciation', function() {

    var id = $(this).data('id');
      arr_advantages = arr_advantages.filter(function(data){
        return data.id != id;
      })
    content_apprecitation[0] = arr_advantages;
    reloadAppreciation(arr_advantages, 'advantages');
  })

  $(document).on('click','#disadvantages-content td .btn-remove-appreciation', function() {

    var id = $(this).data('id');
      arr_disadvantages = arr_disadvantages.filter(function(data){
        return data.id != id;
      })
    content_apprecitation[1] = arr_disadvantages;
    reloadAppreciation(arr_disadvantages, 'disadvantages');
  })


  function updateSortAdvantages(){
    var newSort = [];
    $('#advantages-content td .btn-remove').each(function(item){
      newSort.push($(this).data('id'));
    })
    arr_advantages =  sortArrById(newSort,arr_advantages); // use

    content_apprecitation[0] = arr_advantages;
    reloadAppreciation(arr_advantages, 'advantages');
  }

  function updateSortDisadvantages(){
    var newSort = [];
    $('#disadvantages-content td .btn-remove').each(function(item){
      newSort.push($(this).data('id'));
    })
    arr_disadvantages =  sortArrById(newSort,arr_disadvantages); // use

    content_apprecitation[1] = arr_disadvantages;
    reloadAppreciation(arr_disadvantages, 'disadvantages');
  }


  $("#advantages-content").sortable({
      connectWith: '.space',
      tolerance: 'intersect',
      update: function(event, ui) {
        updateSortAdvantages();
      }
    });

  $("#disadvantages-content").sortable({
      connectWith: '.space',
      tolerance: 'intersect',
      update: function(event, ui) {
        updateSortDisadvantages();
      }
    });


  function sortArrById(arrId, Arr){
    var newSortArr = []
    $.each(arrId,function(idx,val){
        var Obj = Arr.filter(function(data){
          return val == data.id;
        })
         newSortArr.push(Obj[0]);
    })
    return newSortArr;
  }
