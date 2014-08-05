<? function feedback_snippets($val)
{

$snippets2	= getCacheValue('localSnippets');
if (!$snippets2) $snippets2 = array();

$n		= '{{feedback:display:';
$nLen	= strlen($n);
foreach($snippets2 as $name=>$code){
	if(strncmp($n, $code, $nLen)) continue;
	addSnippet($name, '');
}

	
$files		= array();
$adminFiles	= getFiles(cacheRootPath."/feedback/", "txt$");
$userFiles	= getFiles(images."/feedback/", "txt$");

foreach($adminFiles as $name => $path){
	$name = preg_replace('#\..*#', '', $name);
	$files[$name] = $path;
}
foreach($userFiles as $name => $path){
	$name = preg_replace('#\..*#', '', $name);
	$files[$name] = $path;
}
foreach($files as $path){
	$form		= readIniFile($path);
	$snippetName= $form[':']['snippetName'];
	$formName	= trim($form[':']['name']);
	if ($snippetName) addSnippet($snippetName, "{"."{feedback:display:$formName}"."}");
}

}?>