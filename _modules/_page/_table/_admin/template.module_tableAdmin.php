<? function module_tableAdmin($name, $data)
{
	$menu = $data['adminMenu'];
	if (!is_array($menu)) $menu = array();

	$menu[':type']		= $data['bottom']?'bottom':'';
	$menu[':class'][]	= 'adminGlobalMenu';
	$menu['Изменить таблицу#']	= array(
		'target'	=> '_new',
		'href'		=> getURL("table_edit_$name")
	);
	
	beginAdmin($menu);
	
	$val	= module("read_get:$name");
	echo module("text:split|table", $val);

	endAdmin();
//	module_table_edit($name);;
}?>

<?
//	+function module_table_edit
function module_table_edit($name, $data)
{
	if ($data) $name = $data[1];
	if (!access('write', "text:$name")) return;
	$bAjax	= testValue('ajax');
	
	if (testValue('tableDocument')){
		module("read_set:$name", getValue('tableDocument'));
		if ($bAjax) return module('message', 'Документ сохранен');
	}
	
	m('script:ajaxForm');
	m('page:title', "Изменить таблицу $name");

	$data			= array();
	$data['cols']	= 3;
	$json	= json_encode($data);
	$val	= module("read_get:$name");
?>
<link rel="stylesheet" type="text/css" href="../../_templates/baseStyle.css"/>
<form action="{{url:table_edit_$name}}" method="post" id="formRead" class="admin ajaxForm">
{{editor:table}}
<div class="adminEditTools">
    <p class="adminEditorTools" align="right">
        <input type="submit" value="Сохранить" class="button" />
    </p>
</div> 
<textarea class="input w100 tableEditor" rel="{$json}" name="tableDocument" rows="20">{$val}</textarea>
</form>
<? } ?>


