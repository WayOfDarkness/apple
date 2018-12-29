function makeMap(location){
  var zoom = 18;
  if (!location) {
    var location = {
      lat: 10.8230989,
      lng: 106.6296638
    }
    zoom = 10;
  }

  var map = new google.maps.Map(document.getElementById('myMap'), {
    zoom: zoom,
    center: location
  });
  var marker = new google.maps.Marker({
    position: location,
    draggable: true,
    animation: google.maps.Animation.DROP,
    map: map
  });
  google.maps.event.addListener(marker, 'dragend', function () {
    moveMarker(marker.getPosition());
    latlng = marker.position.lat() + ',' + marker.position.lng();
    console.log(latlng);
    $('.box-custom-field .box-object-setting').attr('data-value', latlng);
  });

  var autocomplete = new google.maps.places.Autocomplete($("#google-address")[0], {});

  google.maps.event.addListener(autocomplete, 'place_changed', function () {
    var place = autocomplete.getPlace();
    initMaps(place.formatted_address);
    latlng = autocomplete['lat'] + ',' + autocomplete['lng'];
  });
}

function initMaps(address) {
  if (!address) address = 'Quận 1, Hồ Chí Minh';
  $.ajax({
    type: 'GET',
    url: 'https://maps.googleapis.com/maps/api/geocode/json?address=' + address + '&key=AIzaSyAoK_hfMfMj30B0PPgJ0qg9sA6kjPVO6QA',
    success: function (json) {
      if (json.status === 'OK') {
        var location = json.results[0].geometry.location;
        latlng = location['lat'] + ',' + location['lng'];
        $('.box-custom-field .box-object-setting').attr('data-value', latlng);
        var map = new google.maps.Map(document.getElementById('myMap'), {
          zoom: 18,
          center: location
        });
        var marker = new google.maps.Marker({
          position: location,
          draggable: true,
          animation: google.maps.Animation.DROP,
          map: map
        });
        google.maps.event.addListener(marker, 'dragend', function () {
          moveMarker(marker.getPosition());
          latlng = marker.position.lat() + ',' + marker.position.lng();
        });

        var autocomplete = new google.maps.places.Autocomplete($("#google-address")[0], {});

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
          var place = autocomplete.getPlace();
          makeMap(place.formatted_address);
          latlng = autocomplete['lat'] + ',' + autocomplete['lng'];
        });
      } else {
        toastr.error('Không thể định vị, vui lòng thử lại!');
      }
    }
  })
}

function moveMarker(pos) {
  geocoder = new google.maps.Geocoder();
  geocoder.geocode({
      latLng: pos
    },
    function (results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        $("#google-address").val(results[0].formatted_address);
        console.log("Test 4");

      } else {
        toastr.error('Không thể định vị!');
      }
    }
  );
};


$(window).on('load', function() {

  if ($(".box-custom-field .chosen-select").length) {
    $(".box-custom-field .chosen-select").chosen();
  }

  initTinymce(".box-custom-field .editor");

  $('.box-custom-field .box-object-setting').each(function(){
    var handle = $(this).data('handle');
    var map_data = $(this).data('value');

    var tempLatLng = map_data[0].latLng;
    var address = map_data[0].address;
    console.log(tempLatLng);
    if (tempLatLng) {
      var latLng = {
        lat: tempLatLng.split(',')[0] - 0,
        lng: tempLatLng.split(',')[1] - 0
      }
      console.log(latLng);
      makeMap(latLng);
      $('#google-address').val(address);
    }
    else{
      makeMap();
    }

  });

  $('.regions').change(function() {
    var id = $(this).find('option:selected').data('id');
    var box = $(this).closest('.box-custom-field');
    StoreAPI.getSubRegion(id, function(res) {
      if (!res.code) {
        if (res.data && res.data.length) {
          var options = '';
          $.each(res.data, function(index, elem) {
            options += '<option value="'+elem.name+'">'+elem.name+'</option>';
          });
          var subregion = box.find('.subregion');
          box.find('.subregion').html(options);
          if (box.find('.subregion').attr('data-value')) {
            box.find('.subregion').val(box.find('.subregion').data('value'));
            box.find('.subregion').attr('data-value', '');
          } else {
            box.find('.subregion').val(box.find('.subregion').find('option').first().html());
          }
        }
      }
    });
  });

  if ($('.regions').length && $('.regions').data('value')) {
    setTimeout(function() {
      $('.regions').trigger('change');
    }, 200);
  }
  $('')

  setTimeout(function() {
    var hostname = location.protocol + '//' + location.host;
    $('.content').find('.value.file').each(function () {
      var file_name = $(this).data('value');
      if (file_name) {
        var link = hostname + '/files/' + file_name;
        $(this).parent().append('<p class="link" style="margin-top: 10px;">Link file: <a target="_blank" href="' + link + '">' + link + '</a><i class="remove-file ico-times"></i></p>');
      }
    });
  }, 1000);
});
if ($(document).find('div[data-type="map"]').length) {
   $.getScript("https://maps.googleapis.com/maps/api/js?key=AIzaSyAkK1Nj9HWtb4R0crJISga3j9hq2aBC8lQ&libraries=places&callback=makeMap");
}
