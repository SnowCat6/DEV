<? function module_tableAdmin($name, $options)
{
	$menu = $options['adminMenu'];
	if (!is_array($menu)) $menu = array();

	$menu[':type']		= $options['bottom']?'bottom':'';
	$menu[':class'][]	= 'adminGlobalMenu';
	$menu['Изменить таблицу#inlineTableEditor']	= array(
		'target'	=> '_new',
		'href'		=> getURL("table_edit_$name", $options)
	);
	
	$op				= $options;
	$op['inline']	= '';
	
	m('script:ajaxForm');
	m('editor:table');
	
	beginAdmin($menu);
	
	$data			= array();
	$data['cols']	= 3;
	$data['dataName']	= 'tableDocument';
	$data['action']	= getURL("table_edit_$name", $op);
	$json			= json_encode($data);
	$val			= module("read_get:$name");
	$fx				= $options['fx'];
	meta::begin(array(
		':tableSource'	=> $name
	));
?>
<div class="inlineTableEditor">
    <textarea class="inlineTableData" rel="{$json}" style="display:none">{$val}</textarea>
    {{text:split|$fx|table|show=$val}}
</div>
<?
	meta::end();
	endAdmin();
}?>

<?
//	+function module_table_edit
function module_table_edit($name, $data)
{
	if ($data) $name = $data[1];
	if (!access('write', "text:$name")) return;

	if (testValue('inline'))
	{
		setTemplate('');
		$options		= array();
		$options['fx']	= getValue('fx');
		module("read_set:$name", getValue('document'));
		return module_tableAdmin($name, $options);
	}

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


