<widget:mapYandex
	category= "Карты"
    name	= "Карта Yandex с адресом"
	cap		= "map"
>
<cfg:data.style.height	name= "Высота окна карты" default="450px" />
<cfg:data.title			name = "Название адреса" /> 
<cfg:data.note			name = "Описание" type="textarea" /> 
<cfg:data.address		name = "Адреса улиц" type="textarea" /> 

<? function widget_mapYandex($id, $data)
{
	m('script:jq');
	m('scriptLoad', '//api-maps.yandex.ru/2.0.31/?load=package.standard,package.geoQuery&lang=ru-RU');
	
	$json	= array();
	foreach(explode("\r\n", $data['address']) as $address)
	{
		$address	= trim($address);
		if (!$address) continue;
		
		$json[$address]	= array(
			'title'	=> $data['title'],
			'note'	=> $data['note']
		);
	};
	$json	= json_encode($json);
?>
<link rel="stylesheet" type="text/css" href="css/yandexMap.css">
<script type="text/javascript" src="script/yandexMap.js"></script>
<div class="yandexMap" id="yandexMap_{$id}" rel="{$json}" {!$data[style]}>
</div>

<? } ?>

</widget:mapYandex>