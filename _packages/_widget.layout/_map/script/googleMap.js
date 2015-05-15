// JavaScript Document

function isTouchEnabled() {
	return !!document.createTouch;
}
$(function()
{
	$(".googleMap").each(function()
	{
		var addresses = $.parseJSON($(this).attr("rel"));
		makeGoogleMap($(this), addresses);
	});
});

//	address{title,note,url}
function makeGoogleMap(thisElm, addresses)
{
	var geocoder = new google.maps.Geocoder();
	if (!geocoder) return;

	var map = null;
	var infowindow = null;
	var bounds = new google.maps.LatLngBounds();
	for(var n in addresses)
	{
		geocoder.geocode({ 'address': n },
			function(results, status)
			{
				if (status != google.maps.GeocoderStatus.OK) return;

				var c = results[0];
				bounds.extend(c.geometry.location);
				
				if (map == null)
					map = makeGoogleMapObjext(thisElm, c);

				var marker = new google.maps.Marker({
					position: c.geometry.location,
					map: map,
					title: c.formatted_address
				}); 
				google.maps.event.addListener(marker, 'click', function() {
					// Check to see if we already have an InfoWindow
					if (!infowindow) {
						infowindow = new google.maps.InfoWindow();
					}
					
					// Setting the content of the InfoWindow
					var ctx = addresses[n];
					var ctx = 	"<b>" + ctx['title'] + "</b>" +
								"<p>" + c.formatted_address + "</p>" + 
								"<p>" + ctx['note'] + "</p>";
					infowindow.setContent(ctx);
					
					// Tying the InfoWindow to the marker 
					infowindow.open(map, marker);
				});
				map.fitBounds(bounds);
			});
		};
};
function makeGoogleMapObjext(thisElm, c)
{
    var mapOptions = {
		zoom: 16,
		center: c.geometry.location,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		scrollwheel: isTouchEnabled()?true:false
	}
	return new google.maps.Map(thisElm.get(0), mapOptions);
}