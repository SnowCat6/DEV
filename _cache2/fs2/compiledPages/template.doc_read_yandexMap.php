<?
function doc_read_yandexMap_beginCache($db, $val, &$search)
{
	m('script:readYandexmap');
	return hashData($search);
}
function doc_read_yandexMap($db, $val, &$search)
{
	m('script:readYandexmap');

	$array	= '';
	$ctx	= '';
	$bShowPopup	= $db->rows() == 1?'true':'false';
	while($data = $db->next())
	{
		$places	= explode("\r\n", $data['fields']['any']['places']);
		foreach($places as $address){
			$address	= trim($address);
			if (!$address) continue;
			
			if ($array) $array .= ', ';
			$array	.= '"'.str_replace('"', '\\"', $address).'"';
			$array	.= ': ';
			
			$url	= getURLEx($db->url());
			$title	=	$data['title'];
			$title	= "<a href=\"$url\">$title</a>";
			$array	.= '"'.str_replace('"', '\\"', rawurlencode($title)).'"';
			
			if ($ctx) $ctx .= ', ';
			$note	= nl2br($data['fields']['any']['note']);
			$ctx	.= '"'.str_replace('"', '\\"', rawurlencode($note)).'"';
		}
	}
?>

<div id="map" style="width: 100%; height: 600px; display:none"></div>
<script type="text/javascript">
ymaps.ready(function(){
	var myMap	= null;
	var a		= {<?= $array?>};
	var ctx		= [<?= $ctx?>];

	for(var n in a)
	{
		ymaps.geocode(n, {results: 1})
		.then(function(res)
		{
			var n	= res.metaData.geocoder.request;
			var c	= res.geoObjects.get(0);
			
			var ix = 0;
			for(var name in a){
				if (n == name){
					name	= decodeURIComponent(a[name]);
					content	= decodeURIComponent(ctx[ix]);
					c.properties.set('balloonContentHeader', name);
					c.properties.set("balloonContentFooter", content);
					break;
				}
				++ix;
			}

			if (myMap == null)
			{
				$("#map").show();
				myMap = new ymaps.Map('map', {
					center: c.geometry.getCoordinates(),
					zoom: 13,
					behaviors: ['dblClickZoom', 'multiTouch']
				});
				myMap.controls
					// Кнопка изменения масштаба.
					.add('zoomControl', { left: 5, top: 5 })
					// Список типов карты
					.add('typeSelector')
					// Стандартный набор кнопок
					.add('mapTools', { left: 35, top: 5 });

				myMap.geoObjects.add(c);
		        if (<?= $bShowPopup?>) c.balloon.open();
			}else{
				myMap.geoObjects.add(c);
				myMap.setBounds(myMap.geoObjects.getBounds());
			}
		});
	}
});
</script>
<? } ?><? function script_readYandexmap($val){ ?>
<script src="//api-maps.yandex.ru/2.0.31/?load=package.standard,package.geoQuery&lang=ru-RU" type="text/javascript"></script>
<? } ?>
