<? function holder_widgetLoad($val, $data)
{
	setTemplate('');
	if (!access('write', "holder:")) return;
	
	$ids	= getValue('ids');
	if ($ids){
		$holderName	= getValue('holderName');
		if (!$holderName) return;
		
		$ids		= explode(',', $ids);

		$widgets	= array();
		foreach($ids as $widgetID)
		{
			$widgets[]	= module("holderAdmin:getWidget:$widgetID");
		}
		module("holderAdmin:setHolderWidgets:$holderName", $widgets);
		
		foreach($ids as $widgetID){
			module("holderAdmin:menuWidget:$widgetID");
		};
		return;
	}
	
	$widgetID	= getValue("id");
	module("holderAdmin:menuWidget:$widgetID");;
}?>