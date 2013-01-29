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
	
	if (testValue('document'))
	{
		$val = getValue('document');
//		if (getValueEncode())	$val = iconv(getValueEncode(), 'UTF-8', $val);
		
		module('prepare:2local', &$val);
		if (file_put_contents_safe(images."/$textBlockName", $val)){
			event('document.compile', &$val);
			setCacheValue("text/$textBlockName", $val);
			if ($bAjax) return module('message', 'Документ сохранен');
		}
	}
	
	$folder	= images."/$name";
	@$val	= file_get_contents(images."/$textBlockName");
	module('prepare:2public', &$val);
	module('script:jq');
	module("editor:$folder");
	$class	= $bAjax?' class="admin ajaxForm"':'class="admin"';
?>
<link rel="stylesheet" type="text/css" href="../../_templates/DEV_style.css"/>
<form action="<?= getURL("read_edit_$name")?>" method="post" id="formRead"{!$class}>
    <textarea name="document" id="documentRead" rows="35" class="input w100"><?= $val ?></textarea>
</form>
<? } ?>
