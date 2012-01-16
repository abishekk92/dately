var resultref;
var marker;
var markersArray = [];
var shitCounter = 0;
var iteration = 0;
var drinkIcon = 'images/drinks.png';
var homeIcon = 'images/home.png';
var resultsStore;
var totalResults =[];
var pubResultsStore;
var barResultsStore;
var userLoc;
var currentlatlng;

$wait = $('#wait');
$locationBar = $('#locationBar')
//map stuff
//
//declare b&w google maps
var lowSat = [{featureType: "all",stylers: [{ saturation: -100 }]}];
//set map options
var myOptions = {
	zoom: 10,
	mapTypeId: google.maps.MapTypeId.ROADMAP,
	styles: lowSat,
	mapTypeControl: false,
	panControl: false,
	zoomControl: true,
	mapTypeControl: false,
	scaleControl: false,
	streetViewControl: false,
	overviewMapControl: false
};

//create map
map = new google.maps.Map(document.getElementById("map"), myOptions);
//other map stuff
geocoder = new google.maps.Geocoder();
var service = new google.maps.places.PlacesService(map);


//kick shit off 
$(document).ready(function(){
	//detect string ?wherethefuck to trigger manual location entry
	var str = window.location.href;
	var substr = str.split('?');
	if(substr[1] == "wherethefuck"){			
		$locationBar.css("opacity",1);
		console.log ("manual location entry")
		
	}
	//otherwise trigger browser geolocation
	else{getLocation();}
});

//if browser has geolocation then get current location
function getLocation() {
	if (Modernizr.geolocation) {
		navigator.geolocation.getCurrentPosition(currentLocation, handle_error,   {timeout:10000});
		console.log ("browser has geolocation")
		}
	else {
		//trigger manual entry if no geolocation
		console.log("browser has geolocation");
		$locationBar.fadeIn();
	}
}

//if geolocation error trigger manual entry
function handle_error(err) {
	$locationBar.css("opacity",1);
	showError("can't find your fucking location");
	console.log ("can't find location")
	if (err.code == 0) {
		console.log("unknown")
	}
	if (err.code == 1) {
		console.log("denied")
	}
	if (err.code == 2) {
		console.log("unreliable")
	}
	if (err.code == 3) {
		console.log("taking ages")
	}
}

//get current location
function currentLocation(position){
	$wait.fadeIn(function(){
		$("#locationBar").fadeOut();
		var latitude = position.coords.latitude;
		var longitude = position.coords.longitude;
		currentlatlng = new google.maps.LatLng(latitude, longitude);
		console.log ("current position is: " + currentlatlng)
		getPlaces(currentlatlng);
	});
	
}

//set home position and request for bars
function getPlaces(currentlatlng) {
	userLoc = currentlatlng;
	homeMarker = new google.maps.Marker({
		map: map,
		animation: google.maps.Animation.DROP,
		position:currentlatlng,
		icon:homeIcon
		});
	var requestBar = {
		location: currentlatlng,
		radius: 1000,
		keyword:"coffee"
		};
	service.search(requestBar, storeRequestBar);
}

//store the results for bars then call for pubs
function storeRequestBar(request) {
	//if (status == google.maps.places.PlacesServiceStatus.OK)
	barResultsStore = request;
	var requestPub = {
	location: currentlatlng,
	radius: 1000,
	keyword: "bar"
	};
	service.search(requestPub, storeRequestPub);
}

//store the results for pubs & combine
function storeRequestPub(request) {
	//if (status == google.maps.places.PlacesServiceStatus.OK)
	pubResultsStore = request;
	//combine pubs & bars into one array
	totalResults = barResultsStore.concat(pubResultsStore)
	console.log("there are " + totalResults.length + "pubs & bars")
	//remove duplicate results
	resultsStore = removeDupes(totalResults, 'id');
	//randomise results
	resultsStore = resultsStore.sort(function(a, b){
		return Math.random() - 0.5
		});	
	for (i=0;i<resultsStore.length;i++) {
		console.log(resultsStore[i].name)
		}
	if (resultsStore == 0) {
		showError("can't find shit there. try somewhere else");
		} 
	chooseBar(resultsStore);	
}

//choose a bar 
function chooseBar(results) {
	barRef = {reference: results[shitCounter].reference}
	service.getDetails(barRef, showBar);
}

//show bar details
function showBar(place, status) {
	if (status == google.maps.places.PlacesServiceStatus.OK) {
		//clear markers and set marker for chosen bar
		for (i in markersArray) {
      		markersArray[i].setMap(null);
    		}
		drinkMarker = new google.maps.Marker({
			map: map,
			animation: google.maps.Animation.DROP,
			position: place.geometry.location,
			icon:drinkIcon
		});
		markersArray.push(drinkMarker);
		
		//get directions and show on map
		placeName = place.name;
		calcRoute(userLoc, place.geometry.location);
		directionsDisplay.setMap(null);
		//hide markers for directions
		directionsDisplay.suppressMarkers = true;
		//show custom polyline
		directionsDisplay.polylineOptions = {
			strokeColor: '#ff8400',
			strokeOpacity: 0.8,
			strokeWeight: 5
			}; 
		directionsDisplay.setMap(map);
		
		//if there's a website - show url
		if (place.website){
			placeSite = place.website;
			}
		//otherwise show url to google places
		else {
			placeSite = place.url;
			
		}
		//add name and address details
		placeAddress = place.formatted_address;
		$("#destination").html("Why don't you guys go to <br/><a href='" + placeSite + "' target='_blank' title='VISIT THE FUCKING WEBSITE'>" + placeName + "</a>")
		$("#address").html(placeAddress);
		$("#actions, #about, #recommendation")
			.fadeIn(function(){
				$wait.fadeOut();});
	}
}


//get directions
var directionsDisplay =  new google.maps.DirectionsRenderer();
var directionsService = new google.maps.DirectionsService();
function calcRoute(start, end) {
  	var request = {
    	origin:start,
    	destination:end,
    	travelMode: google.maps.TravelMode.WALKING
  	};
	directionsService.route(request, function(result, status) {
    	if (status == google.maps.DirectionsStatus.OK) {
      		directionsDisplay.setDirections(result);
			//strokeColor
    	}
  	});
}

 
//manual geolocation
function codeAddress() {
	$wait.fadeIn(function(){
	$locationBar.fadeOut();
	var address = document.getElementById("location").value;
	geocoder.geocode( { 'address': address}, function(results, status) {
		console.log("manual location");											  		
		if (status == google.maps.GeocoderStatus.OK) {
			//console.log(results[0].geometry.location);
			currentlatlng = results[0].geometry.location;
			getPlaces(currentlatlng); 
		}
		else {
			console.log("Geocode was not successful for the following reason: " + status);
			showError("can't find your fucking location. try again");
		}
		});});
}

//choose another bar
$("#shit").click(function(){
	//if there's still more bars in the array, choose the next one					  
	if (shitCounter < (resultsStore.length - 1)) {
		shitCounter++;
	}
	//otherwise the guy is a loser. He doesn't deserve to go on a date
	else 
       {
             $("#shit").html("<a href='#' id='shit' title='Y U NO Haz Money'>Duh,your wallet is already burnt,there is nothing</a>");
             console.log("That guy is a loser,duh");

	}
	console.log(shitCounter);
	chooseBar(resultsStore)
	window.scroll(0,0);
	return false
});


//hide url bar on ios
addEventListener("load", function() {setTimeout(hideURLbar, 0); }, false);
function hideURLbar(){
        window.scrollTo(0,1)
    }
	
//show error messages	
function showError(msg){
	$wait.fadeOut();
	$locationBar.fadeIn();
	$('#error').text(msg).fadeIn();
}


//google geocode autocomplete
var autoOptions = {types: ['geocode']};
var autoInput = document.getElementById('location');
autocomplete = new google.maps.places.Autocomplete(autoInput, autoOptions);

//remove duplicate objects in array
function removeDupes(arr, prop) {
    var new_arr = [];
    var lookup  = {};
 
    for (var i in arr) {
        lookup[arr[i][prop]] = arr[i];
    }
 
    for (i in lookup) {
        new_arr.push(lookup[i]);
    }
 
    return new_arr;
}
