<widget:mapGoogle
	category= "Карты"
    name	= "Карта Google с адресом"
	cap		= "map"
>
<cfg:data.style.height	name= "Высота окна карты" default="450px" />
<cfg:data.title			name = "Название адреса" /> 
<cfg:data.note			name = "Описание" type="textarea" /> 
<cfg:data.address		name = "Адреса улиц" type="textarea" /> 

<? function widget_mapGoogle($id, $data)
{
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

	m('script:jq');
	m('scriptLoad', '//maps.googleapis.com/maps/api/js');
?>
<script src="script/googleMap.js"></script>
<div class="googleMap" id="googleMap_{$id}" rel="{$json|json}" {!$data[style]}>
</div>

<? } ?>

</widget:mapGoogle>