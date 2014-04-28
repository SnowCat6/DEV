<?
function editorInline(&$menu, &$data, $dataSource, $layout)
{
	$inline				= $menu[':inline'];
	$inline['layout']	= $layout;
	$inline['action']	= $inline['inlineAction'];
	
	$dataName			= $dataSource;
	if ($inline['dataPrefix']) $dataName = preg_replace('#^([^\[]+)#', $inline['dataPrefix'], $dataName);
	else $dataName = str_replace('$', '', $dataName);

	$inline['dataName']	= $dataName;
	
	$f	= preg_split('#[$\[\]]#', $dataSource);
	$d	= $data;
	for($ix=2; $ix<count($f); ++$ix){
		if ($f[$ix]) $d = &$d[$f[$ix]];
	}
	$inline['data']	= $d;
	
	$inline['inlineAction']	= '';
	$inline['dataPrefix']	= '';
	moduleEx('editor:inline', $inline);
}
?>