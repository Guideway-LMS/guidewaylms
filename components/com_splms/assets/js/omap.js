/**
 * @package     SP String
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */


 var mymap = L.map('open-map').setView([Joomla.getOptions('lat'),Joomla.getOptions('long')], 13);
 L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=' + Joomla.getOptions('mapbox_api') , {
    maxZoom: 18,
    attribution: 'Map data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors, ' +
      'Imagery © <a href=\"https://www.mapbox.com/\">Mapbox</a>',
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1
  }).addTo(mymap);
  
 if(Joomla.getOptions('address')){
  L.marker([Joomla.getOptions('lat'),Joomla.getOptions('long')]).addTo(mymap)
  .bindPopup(Joomla.getOptions('address')).openPopup();
 }
 else
 {
  L.marker([Joomla.getOptions('lat'),Joomla.getOptions('long')]).addTo(mymap);
 }