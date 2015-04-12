<?
function module_holderAdmin($val, &$data)
{
	list($fn, $val) = explode(':', $val);
	$fn	= getFn("holder_$fn");
	if ($fn) return $fn($val, $data);
}
function holder_findWidget($className, $widget)
{
	if (!$className) $className	= $widget['className'];
	if (!$className) $className	= $widget['name'];
	
	$rawWidgets	= array();
	event('holder.widgets', $rawWidgets);

	foreach($rawWidgets as $rawWidget)
	{
		$rawClassName	= $rawWidget['classname'];
		if (!$rawClassName) $rawClassName = $rawWidget['name'];

		if ($rawClassName && $rawClassName == $className)
			return $rawWidget;
	};
}
function holder_deleteWidget($widgetID, $data)
{
	$widgets	= holder_getWidgets('', '');
	if (!$widgets[$widgetID]) return;
	unset($widgets[$widgetID]);
	holder_setWidgets('', $widgets);
}
function holder_setWidget($widgetID, $widget)
{
	if (!access('write', "holder:")) return;
	
	holderMakeUndo();

	$widgets= getStorage("holder/widgets", 'ini');
	if (!is_array($widgets)) $widgets = array();
	
	$id		= $widgetID;	
	if (!$id)	$id	= $widget['id'];
	if (!$id)	$id	= 'widget_' . time() . rand(100);
	$widget['id']	= $id;

	$widgets[$id]	= module("holderAdmin:widgetPrepare", $widget);
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

	holderMakeUndo();

	$oldWidgets	= getStorage("holder/widgets", 'ini');
	if (!is_array($oldWidgets)) $oldWidgets = array();
	$holders	= getStorage("holder/holders", 'ini');
	if (!is_array($holders)) $holders = array();

	foreach($widgets as $widgetID => $widget)
	{
		$widgets[$widgetID]		= module("holderAdmin:widgetPrepare", $widget);
		$oldWidgets[$widgetID]	= '';
		unset($oldWidgets[$widgetID]);
	}
	
	foreach($oldWidgets as $widgetID => $widget)
	{
		foreach($holders as $holderName => $holds){
			foreach($holds as $ix => $wid){
				if ($wid != $widgetID) continue;
				unset($holds[$ix]);
			}
			$holders[$holderName]	= $holds;
		}
		
		$widget	= module("holderAdmin:widgetPrepare", $widget);
		$delete = $widget[':delete'];
		if (is_array($delete)) module($delete['code'], $delete['data']);
	}
	
	setStorage("holder/widgets", $widgets, 'ini');
	setStorage("holder/holders", $holders, 'ini');
}

function holder_addWidget($holderName, $widgetData)
{
	if (!access('write', "holder:$holderName")) return;

	beginUndo();
	holderMakeUndo();

	$id			= holder_setWidget('', $widgetData);
	$holders	= getStorage("holder/holders", 'ini');
	$holders[$holderName][]	= $id;
	setStorage("holder/holders", $holders, 'ini');
	
	endUndo();

	return $id;
}
function holder_getHolderWidgets($holderName, $data)
{
	$widgets	= getStorage("holder/widgets", 'ini');
	$holders	= getStorage("holder/holders", 'ini');
	$widgetsID	= $holders[$holderName];
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

	beginUndo();
	holderMakeUndo();

	$widgetsID	= array();
	foreach($widgets as $widget){
		$widgetsID[]	= holder_setWidget('', $widget);
	}
	$holders	= getStorage("holder/holders", 'ini');
	$holders[$holderName]	= $widgetsID;
	setStorage("holder/holders", $holders, 'ini');

	endUndo();

	return $widgetsID;
}
function holder_undoWidgets($val, $undo)
{
	if (!access('write', 'undo')) return;

	holderMakeUndo();

	setStorage("holder/widgets", $undo['widgets'], 'ini');
	setStorage("holder/holders", $undo['holders'], 'ini');

	return true;
}
function holderMakeUndo()
{
	$undo	= array(
		'widgets'	=> getStorage("holder/widgets", 'ini'),
		'holders'	=> getStorage("holder/holders", 'ini')
	);
	addUndo("Виджеты измененены", 'holder',
		array('action' => "holderAdmin:undoWidgets", 'data' => $undo)
	);
}
?>