<? function user_edit(&$db, $val, $data){
	@$id = $data[1];
?><? $module_data = array(); $module_data[] = "Редактирование пользователя"; moduleEx("page:title", $module_data); ?><?
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
		$data = $db->openID($id);
	}

	module('script:ajaxForm');
?>
<h2>Ваш логин: <? if(isset($data["login"])) echo htmlspecialchars($data["login"]) ?></h2>
<? module("display:message"); ?>
<form action="<? module("getURL:user_edit_$id"); ?>" method="post" enctype="multipart/form-data" class="admin ajaxForm">
<input type="hidden" name="docSave"  />
<? moduleEx('admin:tab:user_property', $data)?>
</form>
<? } ?>