<?
function module_holder($holderName, $data)
{
	return holder_render($holderName, $data);
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
function holder_render($holderName, $data)
{
	if (access('design', "holder:$holderName"))
	{
		$menu	= array();
		$menu[':type']	= 'left';
		$menu[':class']	= 'adminHolderMenu';
		$menu['Изменить контейнер#ajax']	= getURL('admin_holderEdit', array('holderName' => $holderName));
	}
	
	beginAdmin($menu);
	
	$widgets	= getStorage("holder/widgets", 'ini');
	$widgetsID	= getStorage("holder/$holderName", 'ini');
	if (!is_array($widgetsID)) $widgetsID = array();

	foreach($widgetsID as $widgetID)
	{
		$widget	= $widgets[$widgetID];
		$exec	= $widget[':exec'];
		if ($exec['code']) module($exec['code'], $exec['data']);
	}
	
	endAdmin();
}
?>