<?
function module_read_edit($name, $data)
{
	$name	= $data[1];
	if (!access('write', "text:$name")){
		module('message:error', "Нет прав для редактирования $name");
		return module('page:display:message');
	}

	$bAjax			= testValue('ajax');
	$edit			= getValue('edit');

	if (testValue('delete'))
	{
		module("read_delete:$name");
		module('message', 'Текст удален');
		return module("display:message");
	}
	
	if (testValue('document'))
	{
		$val 	= getValue('document');
		moduleEx('prepare:2local',	$val);
		module("read_set:$name",	$val);
		if ($bAjax) return module('message', 'Документ сохранен');
	}
	
	$val	= module("read_get:$name");
	moduleEx('prepare:2public', $val);
	
	module('script:jq');
	module('script:ajaxForm');
	module("editor", $folder);
	m('page:title', "Изменить текст $name");
	
	$qs	= makeQueryString(array('edit' =>$edit));
?>
<link rel="stylesheet" type="text/css" href="../../_templates/baseStyle.css"/>
<form action="{{url:read_edit_$name=$qs}}" method="post" id="formRead" class="admin ajaxForm">
<div class="adminEditTools">
{{editor:tools:document=folder:$folder/Image}}
</div>
<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
    <textarea name="document" {{editor:data:$folder=$edit}} rows="35" class="input w100 editor"><?= $val ?></textarea>
</div>
</form>
<? } ?>
<?
//	+function module_read_set
function module_read_set($name, $content)
{
	if (!access('write', "text:$name")) return;

	$path	= images."/$name.html";
	$undo	= file_get_contents($path);

	if ($undo == $content || !file_put_contents_safe($path, $content)) return;

	addUndo("'$name' изменен", "read:$name",
		array('action' => "read_undo:$name", 'data' => $undo)
	);
	
	clearCache();
	return true;
}
//	+function module_read_delete
function module_read_delete($name, $content)
{
	if (!access('write', "text:$name")) return;

	$path	= images."/$name.html";
	$folder	= images."/$name";

	beginUndo();

	$undo	= file_get_contents($path);
	addUndo("'$name' удален", "read:$name",
		array('action' => "read_undo:$name", 'data' => $undo)
	);
	
	m('file:unlink', $folder);
	unlink($path);

	endUndo();
	
	clearCache();
	return true;
}
//	+function module_read_get
function module_read_get($name, $content)
{
	$folder	= images."/$name";
	makeDir($folder);

	$path	= images."/$name.html";
	return file_get_contents($path);
}
?>

<?
//	+function module_read_undo
function module_read_undo($name, $data)
{
	if (!access('write', 'undo')) return;

	$undo	= module("read_get:$name");
	module("read_set:$name", $data);	

	return true;
}
?>