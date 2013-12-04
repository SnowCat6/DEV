<? function file_comment($val, &$data)
{
	$file	= $data[1];
	if (!canEditFile($file)) return;
	if (!is_file($file)) return;
	
	if (testValue('fileNote')){
		$ctx	= getValue('fileNote');
		moduleEx('prepare:2local', $ctx);
		event('document.compile', $ctx);
		file_put_contents("$file.html", $ctx);
		
		memClear();	
		if (testValue('ajax')) return module('message', 'Комментарий сохранен');;
		return;
	}
	
	m('page:title', "Комментарий: $file");
	m('script:ajaxForm');
	module("editor");
	
	$ctx	= file_get_contents("$file.html");
	moduleEx('prepare:2public', $ctx);
?>
{{display:message}}
<form method="put" action="{{url:#}}" class="ajaxForm">
<textarea name="fileNote" class="editor input w100 " rows="16">{!$ctx}</textarea>
</form>
<? } ?>