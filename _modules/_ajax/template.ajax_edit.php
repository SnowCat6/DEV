<? function ajax_edit(&$data)
{
	@$id = (int)$data[1];
	
	switch(getValue('ajax')){
	//	Добавть к родителю
	case 'itemAdd';
		$s	= getValue('drop_data');
		if (@$s['parent']){
			$s['prop']['parent'] = alias2doc($s['parent']);
			unset($s['parent']);
		}
		if (@$s['parent*']){
			$s['prop']['parent'] = alias2doc((int)$s['parent*']);
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

		if (is_array(@$s['prop']))
		{
			@$s['-property'] = $s['prop'];
			module("doc:update:$id:edit", $s);
			module('display:message');
		}
		break;
	}
	
	setTemplate('');
	$template	= getValue('template');
	return module("doc:read:$template",  getValue('drop_data'));
}?>
