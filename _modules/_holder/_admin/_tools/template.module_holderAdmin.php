<?
function module_holderAdmin($val, &$data)
{
	list($fn, $val) = explode(':', $val);
	$fn	= getFn("holder_$fn");
	if ($fn) return $fn($val, $data);
}

function holder_undoWidgets($val, $undo)
{
	if (!access('write', 'undo')) return;

	widgetHolder::holderMakeUndo();

	setStorage("holder/widgets", $undo['widgets'], 'ini');
	setStorage("holder/holders", $undo['holders'], 'ini');
	$a	= NULL;
	setCacheValue(':holderWidgets', $a);

	return true;
}

function holder_widgetLoad($widgetID, $data)
{
	setTemplate('ajaxResult');
	if (!access('write', "holder:")) return;
	
	$ids	= getValue('ids');
	if (is_array($ids))
	{
		$holderName	= getValue('holderName');
		if (!$holderName) return;
		
		$widgets	= array();
		foreach($ids as $widgetID)
			$widgets[]	= widgetHolder::getWidget($widgetID);
//			$widgets[]	= module("holderAdmin:getWidget:$widgetID");

		widgetHolder::setHolderWidgets($holderName, $widgets);
//		module("holderAdmin:setHolderWidgets:$holderName", $widgets);
		
		foreach($ids as $widgetID){
			module("holderAdmin:uiMenuWidget:$widgetID");
		};
		return;
	}
	
	if (!$widgetID) $widgetID	= getValue("widgetID");
	module("holderAdmin:uiMenuWidget:$widgetID");;
}
?>