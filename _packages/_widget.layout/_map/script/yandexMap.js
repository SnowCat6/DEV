// JavaScript Document

function isTouchEnabled() {
	return document.createTouch != null;
}
ymaps.ready(function()
{
	$(".yandexMap").each(function()
	{
		var addresses = $.parseJSON($(this).attr("rel"));
		makeYandexMap($(this), addresses);
	});
});

//	address{title,note,url}
function makeYandexMap(thisElm, addresses)
{
	var myMap	= null;
	for(var n in addresses)
	{
		var mark = n.split(':');
		if (mark.length == 2)
		{
			var c = new ymaps.Placemark(mark);
			var ctx	= addresses[n];
			c.properties.set('balloonContentHeader', ctx['title']);
			c.properties.set("balloonContentFooter", ctx['note']);

			if (myMap == null){
				myMap = createYandexMapObject(thisElm, c);
//				myMap.behaviors.disable('multiTouch');
//				myMap.behaviors.disable('drag');
			}else{
				myMap.geoObjects.add(c);
				myMap.setBounds(myMap.geoObjects.getBounds());
			}
		}
		
		ymaps.geocode(n, {results: 1})
		.then(function(res)
		{
			var n	= res.metaData.geocoder.request;
			var c	= res.geoObjects.get(0);

			var ctx	= addresses[n];
			c.properties.set('balloonContentHeader', ctx['title']);
			c.properties.set("balloonContentFooter", ctx['note']);

			if (myMap == null)
				myMap = createYandexMapObject(thisElm, c);
			else{
				myMap.geoObjects.add(c);
				myMap.setBounds(myMap.geoObjects.getBounds());
			}
		});
	}
}

function createYandexMapObject(thisElm, c)
{
	var myMap = new ymaps.Map(thisElm.attr("id"),
	{
		zoom: 13,
		center: c.geometry.getCoordinates(),
		behaviors: isTouchEnabled()?['dblClickZoom', 'multiTouch']:['dblClickZoom', 'multiTouch', 'drag']
	});
	myMap.controls
		// Кнопка изменения масштаба.
		.add('zoomControl', { left: 5, top: 5 })
		// Список типов карты
		.add('typeSelector')
		// Стандартный набор кнопок
		.add('mapTools', { left: 35, top: 5 });

	myMap.geoObjects.add(c);
//	if ($bShowPopup) c.balloon.open();
	return myMap;
}