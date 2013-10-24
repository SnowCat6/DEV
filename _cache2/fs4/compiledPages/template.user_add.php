<? function user_add(&$db, $val, $data){ ?><? $module_data = array(); $module_data[] = "Добавить пользователя"; moduleEx("page:title", $module_data); ?><?
	if (!hasAccessRole('admin,developer,accountManager')){
		module('message:error', 'Недостаточно прав доступа');
		module('display:message');
		return;
	}

	$data			= array();
	$data['access']	= 'user';
	
	if (testValue('docSave'))
	{
		moduleEx('admin:tabUpdate:user_property', $data);
		$iid = moduleEx("user:update::add", $data);
		if ($iid){
			//	document added
			module('message', 'Пользователь создан, можете добавить еще.');
		}
		$data['login']	= '';
		$data['passw']	= '';
	}

	module('script:ajaxForm');
?><? module("display:message"); ?>
<form action="<? module("getURL:user_add"); ?>" method="post" enctype="multipart/form-data" class="admin ajaxForm ajaxReload">
<input type="hidden" name="docSave"  />
<? moduleEx('admin:tab:user_property', $data)?>
</form>
<? } ?>