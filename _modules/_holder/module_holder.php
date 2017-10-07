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
		$id		= userID();
		return hasAccessRole('developer') &&
				getStorage('designMode', "user$id") == 'yes';
	}
}
//////////////////////////////////
//	Показать виджеты в указаной зоне
function holder_render($holderName, $data)
{
	//	Имя области по умолчанию
	if (!$holderName) $holderName = 'default';
	
	//	Обнаружить зацикливание области
	$deep	= meta::get(':holders', array());
	if (is_int(array_search($holderName, $deep))){
		echo "<div>Loop holder detected: $holderName</div>";
		return;
	}
	
	//	Если есть права доступа показать меню
	if (access('design', "holder:$holderName"))
		return module("holderAdmin:uiMenu:$holderName");

	$holders	= getStorage('holder/holders', 'ini');
	
	meta::begin($data);
	$deep[]	= $holderName;
	meta::set(':holders', $deep);
	
	$widgetsID	= $holders[$holderName]['widgets'] or array();
	//	Показать виджеты
	foreach($widgetsID as $widgetID){
		module("widget::$widgetID");
	}
	
	meta::end();
}
?>