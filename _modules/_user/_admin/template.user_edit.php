<? function user_edit(&$db, $val, $data){
	@$id = $data[1];
?>
{{page:title=Редактирование пользователя}}
<?
	if (userID() != $id && !hasAccessRole('admin,developer,accountManager')){
		module('message:error', 'Недостаточно прав доступа');
		module('display:message');
		return;
	}

	$data = $db->openID($id);
	if (!$data){
		module('message:error', 'Пользователь не найден');
		module('display:message');
		return;
	}
	
	if (testValue('docSave'))
	{
		$d 				= array();
		$d['user_id']	= $id;
		moduleEx('admin:tabUpdate:user_property', $d);
		$iid = moduleEx("user:update:$id:edit", $d);
		if ($iid){
			//	document added
			module('message', 'Данные обновлены');
			if (testValue('ajax')) return;
		}
		if (testValue('ajax')) return;
		$data = $db->openID($id);
	}

	m('script:ajaxForm');
	m('page:title', "Настройки $data[login]");
?>
{{ajax:template=ajax_edit}}
{{display:message}}
<form action="{{getURL:user_edit_$id}}" method="post" enctype="multipart/form-data" class="admin ajaxForm">
<input type="hidden" name="docSave"  />
<? moduleEx('admin:tab:user_property', $data)?>
</form>
<? } ?>