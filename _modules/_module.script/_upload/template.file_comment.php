<? function file_comment($val, &$data)
{
	$file	= $data[1];
	if (!canEditFile($file)) return;
	if (!is_file($file)) return;
	
	if (testValue('fileNote')){
		$ctx	= getValue('fileNote');
		moduleEx('prepare:2local', $ctx);
		event('document.compile', $ctx);
		file_put_contents("$file.shtml", $ctx);
		
		$ctx	= getValue('fileName');
		file_put_contents("$file.name.shtml", $ctx);
		
		memClear();	
		if (testValue('ajax')) return module('message', 'Комментарий сохранен');;
		return;
	}
	
	$name	= basename($file);
	m('page:title', "Описание: $name");
	m('script:ajaxForm');
	module("editor");
	
	$ctx	= file_get_contents("$file.shtml");
	moduleEx('prepare:2public', $ctx);
	$name	= file_get_contents("$file.name.shtml");
?>
{{display:message}}
<form method="put" action="{{url:#}}" class="ajaxForm">
Название изображения
<div><input type="text" class="input w100" name="fileName" value="{$name}" /></div><br>
Краткое описание изображения
<textarea name="fileNote" class="editor input w100 " rows="20">{!$ctx}</textarea>
</form>
<? } ?>