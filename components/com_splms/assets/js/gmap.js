/**
 * @package     SP String
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */


jQuery(function($){
  google.maps.event.addDomListener(window, 'load', function(){

    var latlng = new google.maps.LatLng($('#splms-event-map').data('lat'), $('#splms-event-map').data('lng'));
    var styles = [{"featureType": "all", "elementType": "all", "stylers": [{"saturation": -100}, {"gamma": 1}]}];

    var mapOptions = {
      center: latlng,
      scrollwheel: false,
      styles: styles,
      zoom: 15,
      zoomControl: false,
      panControl: false,
      streetViewControl: false,
      mapTypeControl: false,
      overviewMapControl: false,
      clickable: false
    };

    var event_addrs = $('.splms-gmap-canvas').data('address');
    var contentString = '<div class="map-info">' + event_addrs +'</div>';
    var infowindow = new google.maps.InfoWindow({
      content: contentString,
      maxWidth: 200
    });


    var map = new google.maps.Map(document.getElementById('splms-event-map'), mapOptions);
    var marker = new google.maps.Marker({position: latlng, map: map});

    var marker = new google.maps.Marker({
      position: latlng,
      map: map
    });

    marker.addListener('click', function() {
      infowindow.open(map, marker);
    });

    map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
    var styledMapType = new google.maps.StyledMapType(
      [{
        "featureType": "water",
        "elementType": "geometry",
        "stylers": [
        {
          "color": "#e9e9e9"
        },
        {
          "lightness": 17
        }
        ]
      },
      {
        "featureType": "landscape",
        "elementType": "geometry",
        "stylers": [
        {
          "color": "#f5f5f5"
        },
        {
          "lightness": 20
        }
        ]
      },
      {
        "featureType": "road.highway",
        "elementType": "geometry.fill",
        "stylers": [
        {
          "color": "#ffffff"
        },
        {
          "lightness": 17
        }
        ]
      },
      {
        "featureType": "road.highway",
        "elementType": "geometry.stroke",
        "stylers": [
        {
          "color": "#ffffff"
        },
        {
          "lightness": 29
        },
        {
          "weight": 0.2
        }
        ]
      },
      {
        "featureType": "road.arterial",
        "elementType": "geometry",
        "stylers": [
        {
          "color": "#ffffff"
        },
        {
          "lightness": 18
        }
        ]
      },
      {
        "featureType": "road.local",
        "elementType": "geometry",
        "stylers": [
        {
          "color": "#ffffff"
        },
        {
          "lightness": 16
        }
        ]
      },
      {
        "featureType": "poi",
        "elementType": "geometry",
        "stylers": [
        {
          "color": "#f5f5f5"
        },
        {
          "lightness": 21
        }
        ]
      },
      {
        "featureType": "poi.park",
        "elementType": "geometry",
        "stylers": [
        {
          "color": "#dedede"
        },
        {
          "lightness": 21
        }
        ]
      },
      {
        "elementType": "labels.text.stroke",
        "stylers": [
        {
          "visibility": "on"
        },
        {
          "color": "#ffffff"
        },
        {
          "lightness": 16
        }
        ]
      },
      {
        "elementType": "labels.text.fill",
        "stylers": [
        {
          "saturation": 36
        },
        {
          "color": "#333333"
        },
        {
          "lightness": 40
        }
        ]
      },
      {
        "elementType": "labels.icon",
        "stylers": [
        {
          "visibility": "off"
        }
        ]
      },
      {
        "featureType": "transit",
        "elementType": "geometry",
        "stylers": [
        {
          "color": "#f2f2f2"
        },
        {
          "lightness": 19
        }
        ]
      },
      {
        "featureType": "administrative",
        "elementType": "geometry.fill",
        "stylers": [
        {
          "color": "#fefefe"
        },
        {
          "lightness": 20
        }
        ]
      },
      {
        "featureType": "administrative",
        "elementType": "geometry.stroke",
        "stylers": [
        {
          "color": "#fefefe"
        },
        {
          "lightness": 17
        },
        {
          "weight": 1.2
        }
        ]
      }],{name: 'Styled Map'});

        map.mapTypes.set('styled_map', styledMapType);
        map.setMapTypeId('styled_map');


  });

});