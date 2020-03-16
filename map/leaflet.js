//???????
var markers = [];

// JSON file to JAVASCRIPT
document.getElementById('load').onclick = function(){
    var files = document.getElementById('selectFiles').files;
    console.log(files);

    if(files.length <= 0){
        return false;
    }

    var fr = new FileReader();

    fr.onload = function(e){

        var result = JSON.parse(e.target.result);
        console.log(result);
        const{name} = result;
        console.log(name);
        console.log(result.locations[0].latitudeE7);
        console.log(result.locations[0].longitudeE7);
        console.log(result.locations[0].timestampMs);

        // Show all Coordinates from JSON file into the leaflet map.
        for (i=0; i<result.locations.length; i++){
            var lat = result.locations[i].latitudeE7;
            var lon = result.locations[i].longitudeE7;
            var id = result.locations[i].timestampMs;
            showMarker(lat, lon, id);
        }
    }
    fr.readAsText(files.item(0));
};


// Creating map options
var mapOptions = {
    center: [38.230462,21.753150],
    zoom: 13
 }

 // Creating a map object
 var map = new L.map('map', mapOptions);

 // Creating a Layer object
 var map_layer = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
 // Adding layer to the map
 map.addLayer(map_layer);

 // Function which converts number to a string and inserting a decimal point in the third place of it.
 function insertDecimal(num) {
    snum = num.toString();
    return snum.slice(0, 2) + "." + snum.slice(2);
 }
// Function which show the position (Marker) of the user on the Map Layer.
function showMarker(latitude, longitude, id){

    lat = insertDecimal(latitude);
    lon = insertDecimal(longitude);

    var mymarker = L.marker([lat,lon]);
    mymarker.addTo(map);
    //map.addLayer(mymarker);

    markers.push(mymarker);

    mymarker._id = id;

    var popupContent =
        '<p>Do you want to delete this marker?</p>' +
        '<button onclick=deleteMarker(' + id + ')>Delete Marker</button>';

    var markerPopup = mymarker.bindPopup(popupContent, {
        closeButton: false
    });
}


function deleteMarker(id){
    console.log(markers);
    var new_markers = [];
    markers.forEach(function(mymarker){
        if (mymarker._id == id)
            map.removeLayer(mymarker);
        else
            new_markers.push(mymarker);
    });
    markers = new_markers;
}

//save button
document.getElementById('save').onclick = function(){
  alert("i will save to database");
};

//--------------------------------------------------------------------- lasso
showMarker(38246397, 21735134, "test_marker");
const lassoControl = L.control.lasso().addTo(map);

function resetSelectedState() {
    map.eachLayer(layer => {
      if (layer instanceof L.Marker) {
        layer.setIcon(new L.Icon.Default());
      }
    });

}
var test_icon = L.icon({
    iconUrl: '../images/marker-icon_red.png',
});

function setSelectedLayers(markers) {
    markers.forEach(marker => {
      //marker.setIcon(new L.Icon.Default({ className: 'selected '}));
      marker.setIcon(test_icon);
    });
}

map.on('lasso.enabled', () => {
  resetSelectedState();
});

map.on('lasso.finished', event => {
    setSelectedLayers(event.layers);
});
