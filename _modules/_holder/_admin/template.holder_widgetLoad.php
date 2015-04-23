<? function holder_widgetLoad($val, $data)
{
	setTemplate('');
	$widgetID	= getValue("id");
	$widget		= module("holderAdmin:getWidget:$widgetID");
	
	$exec	= $widget[':exec'];
	if (!$exec['code']) return;

	module($exec['code'], $exec['data']);
}?>