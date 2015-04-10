<?
function module_holder($holderName, $data)
{
	return holder_render($holderName);
}
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
function holder_render($holderName)
{
	if (access('design', "holder:$holderName"))
	{
		$menu	= array();
		$menu[':type']	= 'left';
		$menu[':class']	= 'adminHolderMenu';
		$menu['Изменить контейнер#ajax']	= getURL('admin_holderEdit', array('holderName' => $holderName));
	}
	
	beginAdmin($menu);
	
	$widgets	= holderPrepare($holderName);
	foreach($widgets as $widget)
	{
		$exec	= $widget['exec'];
		module($exec['code'], $exec['data']);
	}
	
	endAdmin();
}
function holderPrepare($holderName)
{
	$modules	= array();
	$widgets	= getStorage("holder/widgets", 'ini');
	$widgetsID	= getStorage("holder/$holderName", 'ini');
	if (!is_array($widgetsID)) $widgetsID = array();
	
	$bChange	= false;
	foreach($widgetsID as $ix => $widgetID)
	{
		$widget	= $widgets[$widgetID];
		if (is_array($widget['exec'])){
			$modules[]	= $widget;
			continue;
		}

		$bChange		= true;
		if ($widget) $modules[]	= $widgets[$widgetID]	= module('holderAdmin:widgetPrepare', $widget);
		else unset($widgetsID[$ix]);
	}
	if ($bChange){
		setStorage("holder/$holderName", $widgetsID, 'ini');
		setStorage("holder/widgets", $widgets, 'ini');
	}
	
	return $modules;
}
?>