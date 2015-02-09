<? function ajax_edit(&$data)
{
	@$id = (int)$data[1];
	
	switch(getValue('ajax')){
	//	Добавть к родителю
	case 'itemAdd';
		$s	= getValue('drop_data');
		
		if (@$s['parent']){
			$s['prop'][':parent'] = alias2doc((int)$s['parent']);
			unset($s['parent']);
		}
		if (@$s['parent*']){
			$s['prop'][':parent'] = alias2doc((int)$s['parent*']);
			unset($s['parent*']);
		}
		
		if (is_array(@$s['prop']))
		{
			$s['+property'] = $s['prop'];
			m("doc:update:$id:edit", $s);
			module('display:message');
		}
		break;
	//	Удалить от родителя
	case 'itemRemove':
		$s			= getValue('drop_data');
		if (@$s['parent']){
			$s['prop'][':parent'] = alias2doc($s['parent']);
			unset($s['parent']);
		}

		$s2			= getValue('drop_unset');
		if (@$s2['parent']){
			$s['prop'][':parent'] = alias2doc($s2['parent']);
		}
		
		if (is_array(@$s['prop']))
		{
			@$s['-property'] = $s['prop'];
			module("doc:update:$id:edit", $s);
			module('display:message');
		}
		break;
	case 'itemSort':
		docReadSort();
		break;
	}
	
	setTemplate('');
	$template	= getValue('template');
	return module("doc:read:$template",  getValue('drop_data'));
}?>

<? function docReadSort()
{
	$order		= array();
	$orderRaw	= getValue('sort_data');
	if (!is_array($orderRaw)) return;
	
	$ix = 0;
	foreach($orderRaw as $val)
	{
		if (!preg_match('#doc:(\d+)#', $val, $v)) continue;
		
		$id = $v[1];
		if (isset($order[$id])) continue;
		
		$order[$id] = $ix;
		++$ix;
	}
	if (!$order) return;

	$db	= module('doc');
	$db->openIN(array_keys($order));
	while($db->next())
	{
		$id	= $db->id();
		if (!isset($order[$id])) continue;
		
		$d	= array('sort' => $order[$id]);
		m("doc:update:$id:edit", $d);
	}
}?>