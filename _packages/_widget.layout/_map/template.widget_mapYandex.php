<widget:mapYandex
	category= "Карты"
    name	= "Карта Yandex с адресом"
	cap		= "map"
>
<cfg:data.style.height	name= "Высота окна карты" default="450px" />
<cfg:data.title			name = "Название адреса" /> 
<cfg:data.note			name = "Описание" type="html" /> 
<cfg:data.address		name = "Адреса улиц" type="textarea" /> 

<? function widget_mapYandex($id, $data)
{
	$json	= array();
	foreach(explode("\r\n", $data['address']) as $address)
	{
		$address	= trim($address);
		if (!$address) continue;
		
		if (preg_match('#([\d\.]+)\s*,\s*([\d\.]+)#', $address, $val))
		{
			$address	= "$val[1]:$val[2]";
			$json[$address]	= array(
				'title'	=> $data['title'],
				'note'	=> $data['note']
			);
		}else{
			$json[$address]	= array(
				'title'	=> $data['title'],
				'note'	=> $data['note']
			);
		}
	};

	m('script:jq');
	m('scriptLoad', '//api-maps.yandex.ru/2.0.31/?load=package.standard,package.geoQuery&lang=ru-RU');
?>
<link rel="stylesheet" type="text/css" href="css/yandexMap.css">
<script type="text/javascript" src="script/yandexMap.js"></script>
<div class="yandexMap" id="yandexMap_{$id}" rel="{$json|json}" {!$data[style]|style}>
</div>

<? } ?>

</widget:mapYandex>