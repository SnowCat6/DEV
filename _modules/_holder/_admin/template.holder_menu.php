<? function holder_menu($holderName, $data)
{
	if (!access('design', "holder:$holderName")) return;
	
	global $_CONFIG;
	$menu	= array();
	$holders= getStorage('holder/holders', 'ini');

	if ($_CONFIG[':holders'])
	{
		foreach($_CONFIG[':holders'] as $ix => $hn)
		{
			$note	= $holders[$hn]['note'];
			$menu[($ix + 1) . '#ajax']	= array(
				'href'	=> getURL('admin_holderEdit', array('holderName' => $hn)),
				'title'	=> $note
			);
		}
	}
	
	$note			= $holders[$holderName]['note'];
	$menu[':type']	= 'left';
	$menu[':class']	= 'adminHolderMenu';
	$menu['Изменить контейнер#ajax']	= array(
		'href' 	=> getURL('admin_holderEdit', array('holderName' => $holderName)),
		'title'	=> $note
	);
	
	return $menu;
}?>