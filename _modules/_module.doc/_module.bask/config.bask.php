<?
addUrl('bask',				'order:order');
addUrl('bask_clear',		'bask:update:clear');
addUrl('bask_add(\d+)',		'bask:update:add');
addUrl('bask_delete(\d+)',	'bask:update:delete');
addUrl('bask_set(\d+)',		'bask:update:set');

addEvent('config.end',	'bask_config');
function module_bask_config($val, $data)
{
	$docTypes = array();
	$docTypes['catalog']	= 'Каталог:каталогов';
	$docTypes['product']	= 'Товар:товаров';
	m('doc:config:type', $docTypes);
}
?>