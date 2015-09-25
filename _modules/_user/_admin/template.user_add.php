<? function user_add($val, $data){ ?>
{{page:title=Добавить пользователя}}
<?
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
	}

	module('script:ajaxForm');
?>
{{ajax:template=ajax_edit}}
{{display:message}}
<form action="{{getURL:user_add}}" method="post" class="admin ajaxForm ajaxReload">
<input type="hidden" name="docSave"  />
<? moduleEx('admin:tab:user_property', $data)?>
</form>
<? } ?>