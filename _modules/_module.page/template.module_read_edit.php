<?
function module_read_edit($name, $data)
{
	$name	= $data[1];
	if (!access('write', "text:$name")){
		module('message:error', "Нет прав для редактирования $name");
		return module('page:display:message');
	}

	$bAjax			= testValue('ajax');
	$textBlockName	= "$name.html";
	$path			= images."/$textBlockName";
	$folder			= images."/$name";
	
	if (testValue('delete')){
		@unlink($path);
		delTree($folder);
		setCache($textBlockName);
		module('message', 'Текст удален');
		if ($bAjax) return module("display:message");
	}
	
	if (testValue('inline'))
	{
		setTemplate('');
		$val = getValue('editorData');
		moduleEx('prepare:2local', $val);
		if (file_put_contents_safe($path, $val))
			setCache($textBlockName);

		echo 'Документ сохранен';
		return;
	}
	if (testValue('document'))
	{
		$val = getValue('document');
		moduleEx('prepare:2local', $val);
		if (file_put_contents_safe($path, $val))
		{
			setCache($textBlockName);
			if ($bAjax) return module('message', 'Документ сохранен');
		}
	}
	
	@$val	= file_get_contents($path);
	moduleEx('prepare:2public', $val);
	module('script:jq');
	module('script:ajaxForm');
	module("editor", $folder);
	makeDir($folder);
	m('page:title', "Изменить текст $name");
?>
<link rel="stylesheet" type="text/css" href="../../_templates/baseStyle.css"/>
<form action="{{url:read_edit_$name}}" method="post" id="formRead" class="admin ajaxForm pageEdit">
{{editor:images:document=$folder/Image}}
<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
    <textarea name="document" rows="35" class="input w100 editor"><?= $val ?></textarea>
</div>
</form>
<? } ?>
