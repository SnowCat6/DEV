<?
addUrl('file_connector',  			'file:connector');
addUrl('file_fconnector(/.*)',		'file:connector:fck');
addUrl('file_fconnector2(/.*)',		'file:connector:fck2');

addUrl('.*/core/connector/php/connector.*|/file_fconnector2(/.*)',	'file:connector:fck2');

addUrl('file_images',		'editor:images:ajax');

addEvent('page.compile',	'editor_page_compile');
function module_editor_page_compile($val, &$thisPage)
{
	//	inline edit
	//	use:
	//	{beginInline}
	//	any page render code
	//	{endInline:$inlineMenu:$dataSource[field][field]}
	$thisPage	= str_replace('{beginInline}',		'<? ob_start() ?>',		$thisPage);
	$thisPage	= preg_replace('#{endInline:(\$[\w\d_]+):(\$[\w\d_]+)([\d\w_\[\]]*)}#',	'<?
if (\\1[\':inline\']){
	module(\'editor:none\');
	editorInline(\\1, \\2, \'\\2\\3\',ob_get_clean());
}else ob_end_flush();
?>',	$thisPage);
}
?>
