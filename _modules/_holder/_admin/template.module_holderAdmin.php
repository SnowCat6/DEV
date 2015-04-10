<?
function module_holderAdmin($val, &$data)
{
	list($fn, $val) = explode(':', $val);
	$fn	= getFn("holder_$fn");
	if ($fn) return $fn($val, $data);
}
function holder_setWidget($widgetID, $widget)
{
	if (!access('write', "holder:")) return;

	$id		= $widgetID;	
	if (!$id)	$id	= $widget['id'];
	if (!$id)	$id	= 'widget_' . time() . rand(100);
	$widget['id']	= $id;
	
	$widget['config']['Комментарий']['name']	= 'note';
	$widget['exec']	= NULL;
	
	$widgets= getStorage("holder/widgets", 'ini');
	if (!is_array($widgets)) $widgets = array();
	
	$widgets[$id]	= $widget;
	setStorage("holder/widgets", $widgets, 'ini');
	
	return $id;
}
function holder_getWidget($widgetID, $data)
{
	$widgets	= getStorage("holder/widgets", 'ini');
	return $widgets[$widgetID];
}
function holder_getWidgets($val, $data)
{
	$widgets	= getStorage("holder/widgets", 'ini');
	if (!is_array($widgets)) $widgets = array();
	return $widgets;
}
function holder_setWidgets($val, $widgets)
{
	if (!access('write', "holder:")) return;

	setStorage("holder/widgets", $widgets, 'ini');
}

function holder_addWidget($holderName, $widgetData)
{
	if (!access('write', "holder:$holderName")) return;

	$id			= holder_setWidget('', $widgetData);
	$modules	= getStorage("holder/$holderName", 'ini');
	if (!is_array($modules)) $modules	= array();
	$modules[]	= $id;
	$modules	= setStorage("holder/$holderName", $modules, 'ini');

	return $id;
}
function holder_getHolderWidgets($holderName, $data)
{
	$widgets	= getStorage("holder/widgets", 'ini');
	$widgetsID	= getStorage("holder/$holderName", 'ini');
	if (!is_array($widgetsID)) $widgetsID = array();
	
	$modules	= array();
	foreach($widgetsID as $widgetID){
		$modules[] = $widgets[$widgetID];
	}
	return $modules;
}
function holder_setHolderWidgets($holderName, $widgets)
{
	if (!access('write', "holder:$holderName")) return;

	$widgetsID	= array();
	foreach($widgets as $widget){
		$widgetsID[]	= holder_setWidget('', $widget);
	}
	setStorage("holder/$holderName", $widgetsID, 'ini');
	return $widgetsID;
}
?>