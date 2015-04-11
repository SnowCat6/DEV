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
	if (!$holderName) $holderName = 'default';
	
	global $_CONFIG;
	if (is_int(array_search($holderName, $_CONFIG[':holders']))){
		echo "<div>Loop holder detected, $holderName</div>";
		return;
	}
	
	$_CONFIG[':holders'][]	= $holderName;
	
	if (access('design', "holder:$holderName"))
	{
		$menu	= array();
		$menu[':type']	= 'left';
		$menu[':class']	= 'adminHolderMenu';
		$menu['Изменить контейнер#ajax']	= getURL('admin_holderEdit', array('holderName' => $holderName));
	}
	
	beginAdmin($menu);
	
	$widgets	= getStorage("holder/widgets", 'ini');
	$holders	= getStorage("holder/holders", 'ini');
	$widgetsID	= $holders[$holderName];
	if (!is_array($widgetsID)) $widgetsID = array();

	foreach($widgetsID as $widgetID)
	{
		$widget	= $widgets[$widgetID];
		$exec	= $widget[':exec'];
		if ($exec['code']) module($exec['code'], $exec['data']);
	}
	
	endAdmin();
	
	array_pop($_CONFIG[':holders']);
}
?>