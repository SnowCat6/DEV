<? function holder_widgetLoad($widgetID, $data)
{
	setTemplate('ajaxResult');
	if (!access('write', "holder:")) return;
	
	$ids	= getValue('ids');
	if (is_array($ids)){
		$holderName	= getValue('holderName');
		if (!$holderName) return;
		
		$widgets	= array();
		foreach($ids as $widgetID)
			$widgets[]	= module("holderAdmin:getWidget:$widgetID");

		module("holderAdmin:setHolderWidgets:$holderName", $widgets);
		
		foreach($ids as $widgetID){
			module("holderAdmin:uiMenuWidget:$widgetID");
		};
		return;
	}
	
	if (!$widgetID) $widgetID	= getValue("widgetID");
	module("holderAdmin:uiMenuWidget:$widgetID");;
}?>