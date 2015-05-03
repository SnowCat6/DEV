<? function holder_menu($holderName, $data)
{
	if (!access('write', "holder:$holderName")) return;
	
	m('script:jq');
	m('script:overlay');
	m('fileLoad', 'script/widgetAdmin.js');
	
	$menu	= array();
	$holders= getStorage('holder/holders', 'ini');
	$widgets= getCacheValue(':holderWidgets');

	global $_CONFIG;
	$v	= $_CONFIG[':holders'] or array();
	foreach($h as $ix => $hn)
	{
		$note	= $holders[$hn]['note'];
		$menu[($ix + 1) . '#ajax']	= array(
			'href'	=> getURL('admin_holderEdit', array('holderName' => $hn)),
			'title'	=> $note
		);
	}
	
	$note			= $holders[$holderName]['note'];
	$menu[':type']	= 'left';
	$menu[':class']	= 'adminHolderMenu';
	$menu['Изменить контейнер#ajax']	= array(
		'href' 	=> getURL('admin_holderEdit', array('holderName' => $holderName)),
		'title'	=> $note
	);

	$_CONFIG[':holders'][]	= $holderName;
	
	beginAdmin($menu);

	$widgetsID	= $holders[$holderName]['widgets'] or array();
	//	Показать виджеты
	foreach($widgetsID as $widgetID)
	{
		$widget	= $widgets[$widgetID];
		$exec	= $widget[':exec'];
		if (!$exec['code']) continue;
		
		echo "<div class=\"adminWidget\" id=\"$widgetID\">";
		module($exec['code'], $exec['data']);
		echo "</div>";
	}
	
	endAdmin();
	array_pop($_CONFIG[':holders']);
}?>