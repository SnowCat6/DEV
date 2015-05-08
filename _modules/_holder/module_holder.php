<?
//	Область свободного размещения виджетов
function module_holder($holderName, $data)
{
	return holder_render($holderName, $data);
}
//	Права доступа для изменения
function module_holderAccess($access, $data)
{
	switch($access){
	case 'write':
		return hasAccessRole('developer');
	case 'design':
		$ini	= getIniValue(':');
		return hasAccessRole('developer') && $ini['designMode'] == 'yes';
	}
}
//////////////////////////////////
//	Показать виджеты в указаной зоне
function holder_render($holderName, $data)
{
	global $_CONFIG;
	//	Имя области по умолчанию
	if (!$holderName) $holderName = 'default';
	
	//	Обнаружить зацикливание области
	if (is_int(array_search($holderName, $_CONFIG[':holders']))){
		echo "<div>Loop holder detected, $holderName</div>";
		return;
	}
	
	$holders	= getStorage('holder/holders', 'ini');
	$widgets	= getCacheValue(':holderWidgets');
	//	Обновить кеш виджетов
	if (!$widgets)
	{
		$widgets	= getStorage("holder/widgets", 'ini') or array();
		foreach($widgets as &$w)
			$w	= module("holderAdmin:widgetPrepare", $w);
		setCacheValue(':holderWidgets', $widgets);
	}
	//	Если есть права доступа показать меню
	if (access('design', "holder:$holderName"))
		return module("holderAdmin:uiMenu:$holderName");
	
	$_CONFIG[':holders'][]	= $holderName;
	
	$widgetsID	= $holders[$holderName]['widgets'] or array();
	//	Показать виджеты
	foreach($widgetsID as $widgetID)
	{
		$widget	= $widgets[$widgetID];
		$exec	= $widget[':exec'];
		if (!$exec['code']) continue;
		module($exec['code'], $exec['data']);
	}
	array_pop($_CONFIG[':holders']);
}
?>