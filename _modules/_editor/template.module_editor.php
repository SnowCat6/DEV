<?
function module_editor($val, &$baseFolder)
{
	if ($val){
		list($fn, $val) = explode(':', $val, 2);
		$fn = getFn("editor_$fn");
		return $fn?$fn($val, $baseFolder):NULL;
	}
	
	if (is_dir($baseDir = '_editor/ckeditor'))
		return moduleEx("editor:FCK4:$baseDir", $baseFolder);
		
	moduleEx("editor:FCK3", $baseFolder);
}
function editor_data(&$baseFolder, &$inline)
{
	if (!isset($inline['folder'])) $inline['folder'] = $baseFolder;
	$inline['data']		= '';
	$inline['layout']	= '';
	removeEmpty($inline);
	
	$json	= htmlspecialchars(json_encode($inline));
	echo " rel=\"$json\"";
}
function editor_inline(&$baseFolder, &$inline)
{
	$layout	= $inline['layout'];
	$action	= $inline['action'];
	if (!$action){
		echo $layout;
		return;
	}

	if ($inline['data'] == $layout){
		$inline['data'] = '';
		unset($inline['data']);
	}
	if (isset($inline['data']))
	{
		$d		= htmlspecialchars($inline['data']);
		$json	= mEx("editor:data:$baseFolder", $inline);
		echo "<div class=\"inlineEditor\"$json>$layout</div><div id=\"editorData\" style=\"display:none\">$d</div>";
	}else{
		$json	= mEx("editor:data:$baseFolder", $inline);
		echo "<div class=\"inlineEditor\"$json>$layout</div>";
	}
	module('editor');
}

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
