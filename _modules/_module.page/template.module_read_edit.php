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
		module('message', 'Текст удален');
		if ($bAjax) return module("display:message");
	}
	
	$cache			= getCacheValue('textBlocks');
	if (testValue('document'))
	{
		$val = getValue('document');
		
		module('prepare:2local', &$val);
		if (file_put_contents_safe($path, $val))
		{
			event('document.compile', &$val);
			$cache[$textBlockName] = $val;
			setCacheValue("textBlocks", $cache);
			if ($bAjax) return module('message', 'Документ сохранен');
		}
	}
	
	@$val	= file_get_contents($path);
	module('prepare:2public', &$val);
	module('script:jq');
	module("editor:$folder");
?>
<link rel="stylesheet" type="text/css" href="../../_templates/baseStyle.css"/>
<h1>Изменить текст</h1>
<form action="<?= getURL("read_edit_$name")?>" method="post" id="formRead" class="admin ajaxForm">
<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
    <textarea name="document" rows="35" class="input w100 editor"><?= $val ?></textarea>
</div>
</form>
<? } ?>
