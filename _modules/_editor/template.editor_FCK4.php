<? function editor_FCK4(&$baseDir, &$baseFolder)
{
	m("script:jq");
	m("script:ajaxForm");
	m("script:editorFCK4finder",$baseDir);
	m("script:editorFCK4",		$baseDir);

	m("script:fileUpload");
} ?>
<? function script_editorFCK4(&$baseDir)
{
	m('scriptLoad', 'script/CK4.js');
	$rootURL	= globalRootURL;
/******************************/
//	Build CSS JS rules
	$styles		= array();
	$script		= array();
	$styles[]	= "$rootURL/$baseDir/contents.css";
	
	$cssFiles	= getSiteFiles(array('', 'css'), '\.css$');
	foreach($cssFiles as $name=>$path){
		if (makeCKStyleScript($script, $path)){
			$styles[$name]	= "$rootURL/$name";
		}
	}
?>
<script>
/*<![CDATA[*/
var CK4Styles = <?= json_encode(array_values($styles)) ?>;
var CK4Scripts= <?= json_encode(array_values($script)) ?>;
var CK4RootURL = '{$rootURL}/{$baseDir}/';
 /*]]>*/
</script>
<? } ?>
<? function script_editorFCK4finder(&$baseDir)
{
	if (is_dir($baseFinder = '_editor/ckfinder.2.4.2')){
		$cnn	= getURL('file_fconnector2/#folder#');
	}else return;
?>
<script>
/*<![CDATA[*/
var editorBaseFinder = "{$rootURL}/{$baseFinder}/ckfinder.html";
var cnn = editorBaseFinder + "?Connector={$cnn}";
 /*]]>*/
</script>
<? } ?>
<? function makeCKStyleScript(&$script, $cssFile)
{
	$bOK 	= false;
	$f		= file_get_contents($cssFile);
	preg_match_all('#/\* (.*): ([\w]+)\.([\w\d\.]+) \*/#', $f, $vals);
	foreach($vals[1] as $ix => $name)
	{
		$n		= str_replace("'", '"', $name);
		$elm	= $vals[2][$ix];
		$class	= $vals[3][$ix];
		$class	= str_replace('.', ' ', $class);
		$script[$name]	= array(
			'name'		=> $n,
			'element'	=> $elm,
			'attributes'=> array('class' => $class)
		);
		$bOK	= true;
	}
	preg_match_all('#/\* (.*): ([\w]+) \*/#', $f, $vals);
	foreach($vals[1] as $ix => $name)
	{
		$n	= str_replace("'", '"', $name);
		$elm= $vals[2][$ix];
		$script[$name]	= array(
			'name'		=> $n,
			'element'	=> $elm
		);
		$bOK	= true;
	}
	return $bOK;
}?>
