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
function editor_data(&$baseFolder, $inline)
{
	if (!isset($inline['folder'])) $inline['folder'] = $baseFolder;
	$inline['data']		= '';
	$inline['layout']	= '';
	removeEmpty($inline);
	
	$json	= htmlspecialchars(json_encode($inline));
	echo " rel=\"$json\"";
}
function editor_inline(&$baseFolder, $inline)
{
	$layout	= $inline['layout'];
	$action	= $inline['action'];
	if (!$action){
		echo $layout;
		return;
	}

	$json	= m("editor:data", $inline);
	if (isset($inline['data']))
	{
		$d	= htmlspecialchars($inline['data']);
		echo "<div class=\"inlineEditor\"$json>$layout</div><div id=\"editorData\" style=\"display:none\">$d</div>";
	}else{
		echo "<div class=\"inlineEditor\"$json>$layout</div>";
	}
	module('editor');
}
?>
